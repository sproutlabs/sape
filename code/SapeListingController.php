    <?php    

/**
 * The SAPE DataObjectDecorator for the FacetedListingController
 * 
 * Add replace the javascrits 
 *
 * @package 
 */


abstract class SapeListingController extends FacetedListingController {
        
    public function init() {
		parent::init();
                                
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-metadata/jquery.metadata.js');
		//CHANGE so that we use the local .js so that Google DataTables can be made. 
                Requirements::javascript('http://www.google.com/jsapi');
                
                        
                       
                Requirements::javascript('sape/javascript/SapeListing.js');
	}

      
        
        
    /*Function the should overwrite in actual use */
        
    
    /** Returns a list the class that are to be used in the chart 
     *
     * @return array
     * 
     */
       
        
     public function getPlotClasses() {
            return array(
            );
     }
     
     
     public function PlotLabels() {
         
                $columns  = new DataObjectSet();
		$fields   = $this->getPlotClasses();
                
                
                

		foreach ($fields as $name => $title) {
				$columns->push(new ArrayData(array(
				'Name'      => $name,
				'Title'     => $title,
			)));
		}

		return $columns;
                
         
     }
     
     
    
     	/** Returns the data for use in the JSON google chart.
	 * @return DataObjectSet
	 */
	public function VisItems() {
            
		$result = new DataObjectSet();
                $yFound = array();
                
		$items  = $this->getSourceItems();
                
                
                // getPlotClasses()
                $xAxis =  $this->getPlotX();
                
                //Debug::Show($xAxis);

                                
                $completelyPlots = array (); //a new of tracking which one's have been looked at is the past
                
                $row = new DataObjectSet();;//list the diease that have been found 

		if ($items) foreach ($items as $item) {
                    
                   // Debug::show($item->$xAxis);
                    //
                    // check to see if have done this already
                    $done = FALSE;
                    //Debug::show($completelyPlots);
                    foreach ($completelyPlots as $completed) {  
                        if($completed == $item->$xAxis ) {
                           $done = TRUE; 
                           //echo "<p>Already done " . $item->$xAxis;
                           break;
                        } else {
                           $done = FALSE;
                        }
                        
                    }
                    
                    
                   // Only do this the xAxis hasn't been loked at before. 
                    if ($done == TRUE) {
                        //echo "<p>Done this already " . $item->$xAxis  ;
                    } else {
                        
                       //add to what has already been done 
                        array_push($completelyPlots,$item->$xAxis);
                       //Now we do the actual core bit 
                        
                        //Find the other items with the same $item->$xAxis
                        
                                      
                       //Check to see if there any other items with same x Axis
                       $xFound = array();
                       
                       foreach ($items as $SearchItem) {                           
                           if($SearchItem->$xAxis == $item->$xAxis ) {
                             // echo '<p> added that list days ' . $SearchItem->$xAxis ;
                             //$xFound->push($SearchItem->$xAxis);
                             array_push($xFound,$SearchItem);
                           }

                       }
                       //BUG - seems to still be counting twice 
                        //echo '<p> count are ' .  count($xFound);

                       //Now for the day we need look at the dieases and count those.  
                       
                       //echo '<p> '.count($xFound) . ' day '. $xFound[0];
                       
                       //Make the list of y stuff that has been found 
                       $count = 0;// how many times we have found the yPlot  
                       
                       $yCounts = array(); // where the Disease for this day are being stored .
                        
                       foreach ($xFound as $Found) {
                           //lookat the disease that can be found on each
                           
                           foreach ($Found->Diseases() as $Disease) {
                                //Debug::Show($Found->Diseases());//remove the hard coding 
                                //BUG - turn on to see the bug 
                                //echo '<p> Looking at day'. $Found->$xAxis . ' and the disease we have found is ' . $Disease->Name;
                                
                                array_push($yCounts,$Disease->Name);
                               
                                //See if this in existing set of found stuff 
                                 if (in_array($Disease,$yFound)) {
                                        //yes there arealad
                                    } else {
                                        array_push($yFound,$Disease);//$yFound is all disease that have been found for this day
                                  }
                           }
                       }
                       //echo '<p>';
                       
                       //print_r(array_count_values($yCounts));
                       
                       //Count what is in that  
                       $yCounted = array_count_values($yCounts); 
                       
                       //now make that in nice value and count pair. 
                       
                       //look a each of what found build a result object and name and counts on the 
                       //also add the that to the list object as   
                      
                       $list = new DataObjectSet();
                       
                       foreach ($yCounted as $key => $value) {
                           //print "$key == $value <p>"; 
                           $newResult = new SapeResult();
                          
                           $newResult->setField('Count',$value);
                           $newResult->setField('Name',$key);
                           //sDebug::show($newResult);
                           $list->push($newResult);
                           
                       } 
                       
                       //Debug::show($list);
                       
                       
                       //now need to convert this  
                       
                       //print_r(array_count_values($yFound));
                       
                       /*$row->push(new ArrayData(array(
                                    'Colors' => new DataObjectSet(array( 
                                             $xAxis  => $Found->$xAxis,
                                            'Diseases' => $list
                                            //'Diseases' =>  $yCounts,
                                      )))
                                          
                             ));// this do
                             //
                          
                    
                       //*/
                       
                      $row->push(new ArrayData(array( 
                         'x' => new DataObjectSet(array( 
                            array( $xAxis  => $Found->$xAxis), 
                            array('Diseases' => $list) 
                         )) 
                      )));

                               
                      // echo '<p>' . $Found->$xAxis . ' - ';
                       //foreach ($row as $r) {

                         //     echo '<p> -------------  <p>Looking at day '. $r->$xAxis . ' and the disease we have found is '; 
                         //     Debug::show($r->Diseases);
                        //}                            
         
                    }
                    
                   $result->push($row);
                  //make sure if   
                    
			
		}
                ///Debug::show($result);
                //reformat the row's so that eall them have the same dieseases 

                return $row;
                        
                        
                        
                 /*foreach ($row as $r) {
                       
                     //echo '<p>';
                     //print_r($r->Diseases);
                       
                       $DiseaseArray = array();
                       
                       foreach ($yFound as $y) {
                          //echo $y->Name; 
                          
                          foreach ($r->Diseases as $d) {
                              //Debug::Show($d);
                              $name = $y->Name;
                              //echo $d->$name . ' <p>';
                              $currentCount = $d->$name;
                              //echo $currentCount .  ' <p> '; 
                              
                              if($currentCount) {
                                  //echo 'got it <p>';
                                  $DiseaseArray[$name] = $currentCount; ;
                                  
                              } else {
                                  //echo 'not found';
                                  $DiseaseArray[$name] = 0; ;

                              }
                          }
                         
                       
                            
                        };
                        
                        //echo '----------- <p>';
                        
                       // print_r($yCounts);
                         
                        Debug::Show($list);
                         
                        $result->push( 
                             new ArrayData(array(
                                            $xAxis  => $r->$xAxis,
                                            'Diseases' => $list
                                      ))
                                          
                         );
                             
                              //echo '<p> -------------  <p>Looking at day'. $r->$xAxis . ' and the disease we have found is '; 
                              //print_r($r->Diseases);
                        }   */                        
         
                        
                        
                       /*foreach ($result as $r) {

                              echo '<p> -------------  <p>Looking at day'. $r->$xAxis . ' and the disease we have found is '; 
                              print_r($r->Diseases);
                        }*/
                        
                //print_r($result);
                
		//return $result;
	}

	
        
         
}