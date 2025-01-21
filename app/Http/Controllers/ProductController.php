<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Session;

// require 'C:\xampp\composer\vendor\autoload.php';

// require __DIR__.'/../../../../../../composer/vendor/autoload.php';

class ProductController extends Controller
{

    public function showProductInput () {
        Session::pull('errorMessage');
        Session::pull('resultsMessage');
        Session::pull('success');
        Session::pull('error');
        return view ('product_input');
    }

    public function getBoxOptions () {
        $boxOptions = [
            "BOXA" => ['length' => 20, 'width' => 15, 'height' => 10, 'weight_limit' => 5],
            "BOXB" => ['length' => 30, 'width' => 25, 'height' => 20, 'weight_limit' => 10],
            "BOXC" => ['length' => 60, 'width' => 55, 'height' => 50, 'weight_limit' => 50],
            "BOXD" => ['length' => 50, 'width' => 45, 'height' => 40, 'weight_limit' => 30],
            "BOXE" => ['length' => 40, 'width' => 35, 'height' => 30, 'weight_limit' => 20]
        ];

        return $boxOptions;

    }

    public function submitProductInput (Request $request) {

        // dd($request);

        $boxOptions = $this->getBoxOptions();

        $productName = $request->productName;
        $productLength = $request->productLength;
        $productWidth = $request->productWidth;
        $productHeight = $request->productHeight;
        $productWeight = $request->productWeight;
        $productQuantity = $request->productQuantity;

        $totalProductLength = 0;
        $totalProductWidth = 0;
        $totalProductHeight = 0;
        $totalProductWeight = 0;
        $totalProductQuantity = 0;
        $totalProductVolume = 0;

        // IDENTIFY DIMENSIONS OF BOXES
        $boxLengths = array_column($boxOptions, 'length'); 
        $boxWidths = array_column($boxOptions, 'width');
        $boxHeights = array_column($boxOptions, 'height');
        $boxWeightLimits = array_column($boxOptions, 'weight_limit');
        // END OF IDENTOFY DIMENSIONS OF BOXES

        $inputtedProductsMaster = [];

        Session::pull('errorMessage');
        Session::pull('resultsMessage');
        Session::pull('success');
        Session::pull('error');

        for ($i=0; $i<count($productName); $i++) {

            $errorFlag = 0;
            $errorMessage = '';

            // CHECK IF ANY OF THE PRODUCTS HAVE EXCEEDING DIMENSIONS VERSUS BOX SIZE LIMITS
            if ($productLength[$i] > $maxLengthLimit = max($boxLengths)) {
                $errorMessage = $errorMessage.'<li><b>'.$productName[$i].'</b> exceeds the maximum length limit of <u>'.$maxLengthLimit.'cm</u></li>';
                // Session::flash('error', '<b>'.$productName[$i].'</b> exceeds the maximum length limit of <u>'.$maxLengthLimit.'cm</u>');
                $errorFlag++;
            }
            if ($productWidth[$i] > $maxWidthLimit = max($boxWidths)) {
                $errorMessage = $errorMessage.'<li><b>'.$productName[$i].'</b> exceeds the maximum width limit of <u>'.$maxWidthLimit.'cm</u></li>';
                // Session::flash('error', '<b>'.$productName[$i].'</b> exceeds the maximum width limit of <u>'.$maxWidthLimit.'cm</u>');
                $errorFlag++;
            }
            if ($productHeight[$i] > $maxHeightLimit = max($boxHeights)) {
                $errorMessage = $errorMessage.'<li><b>'.$productName[$i].'</b> exceeds the maximum height limit of <u>'.$maxHeightLimit.'cm</u></li>';
                //Session::flash('error', '<b>'.$productName[$i].'</b> exceeds the maximum height limit of <u>'.$maxHeightLimit.'cm</u>');
                $errorFlag++;
            }
            if ($productWeight[$i] > $maxWeightLimits = max($boxWeightLimits)) {
                $errorMessage = $errorMessage.'<li><b>'.$productName[$i].'</b> exceeds the maximum weight limit of <u>'.$maxWeightLimits.'kg</u></li>';
                // Session::flash('error', '<b>'.$productName[$i].'</b> exceeds the maximum weight limit of <u>'.$maxWeightLimits.'kg</u>');
                $errorFlag++;
            }

            if ($errorFlag > 0) {
                if (Session::has('errorMessage')) {
                    Session::put('errorMessage', Session::get('errorMessage').$errorMessage);
                }
                else {
                Session::put('errorMessage', $errorMessage);
                }
            }

            // END OF CHECK IF ANY OF THE PRODUCTS HAVE EXCEEDING DIMENSIONS VERSUS BOX SIZES    

            $inputtedProductsMaster[] = [  
                'product_name' => $productName[$i],
                'product_quantity' => (int)$productQuantity[$i],
                'product_length' => (float)$productLength[$i],
                'product_length_total' => ($productLength[$i] * $productQuantity[$i]),
                'product_width' => (float)$productWidth[$i],
                'product_width_total' => ($productWidth[$i] * $productQuantity[$i]),
                'product_height' => (float)$productHeight[$i],
                'product_height_total' => ($productHeight[$i] * $productQuantity[$i]),
                'product_weight' => (float)$productWeight[$i],
                'product_weight_total' => ($productWeight[$i] * $productQuantity[$i]),
                'product_volume_total' => ($productLength[$i] * $productWidth[$i] * $productHeight[$i]) * $productQuantity[$i]      
            ];


        // GET SUM OF OVERALL PRODUCT DIMENSIONS
            $totalProductLength = $totalProductLength + ((float)($productLength[$i] * $productQuantity[$i]));
            $totalProductWidth = $totalProductWidth + ((float)($productWidth[$i] * $productQuantity[$i]));
            $totalProductHeight = $totalProductHeight + ((float)($productHeight[$i] * $productQuantity[$i]));
            $totalProductWeight = $totalProductWeight + ((float)($productWeight[$i] * $productQuantity[$i]));
            $totalProductQuantity = $totalProductQuantity + (float)$productQuantity[$i];
            $totalProductVolume = $totalProductVolume +  (($productLength[$i] * $productWidth[$i] * $productHeight[$i]) * $productQuantity[$i]);
        // END OF GET SUM OF OVERALL PRODUCT DIMENSIONS
        }

        if ($errorFlag > 0) {
            Session::flash('error', Session::get('errorMessage'));
            return view ('product_input');
        }

        

        // echo "Total Length: ".$totalProductLength."cm<br>";
        // echo "Total Width: ".$totalProductWidth."cm<br>";
        // echo "Total Height: ".$totalProductHeight."cm<br>";
        // echo "Total Weight: ".$totalProductWeight."kg<br>";
        // echo "Total Quantity: ".$totalProductQuantity."pcs<br><br>";

        // CHECK IF THE TOTAL FITS ANY OF THE AVAILABLE BOXES
        $usableBoxes = array();

        foreach ($boxOptions as $boxName => $boxAttributes) {
            // dd($boxName.' => '.$boxAttributes['length']);

            // GET THE TOTAL VOLUME OF THE BOX
            $totalBoxVolume = ($boxAttributes['length'] * $boxAttributes['width'] * $boxAttributes['height']);  

            // GET THE TOTAL VOLUME OF THE PRODUCT
            // $totalProductVolume = ($totalProductLength * $totalProductWidth * $totalProductHeight);

            // GET THE DIFFERENCE BETWEEN THE BOX VOLUME AND PRODUCT VOLUME
            $volumeDifference = ($totalBoxVolume - $totalProductVolume);

            // IF! THE VOLUME DIFFERENCE IS POSITIVE
            if ($volumeDifference >= 0 && $boxAttributes['weight_limit'] >= $totalProductWeight) {
                // CREATE A NEW ARRAY OF USABLE BOXES WITH TOTAL VOLUME             
                $usableBoxesGet = array($boxName => ['box_name' => $boxName,'length' => $boxAttributes['length'], 'width' => $boxAttributes['width'], 'height' => $boxAttributes['height'], 'total_product_weight' => $totalProductWeight, 'weight_limit' => $boxAttributes['weight_limit'], 'total_box_volume' => $totalBoxVolume, 'total_product_volume' => $totalProductVolume, 'volume_difference' => $volumeDifference]);

                $usableBoxes = array_merge($usableBoxes, $usableBoxesGet);

                // END OF CREATE A NEW ARRAY OF USABLE BOXES WITH TOTAL VOLUME
            }
            // END OF IF! THE VOLUME DIFFERENCE IS POSITIVE

        }
        // END OF CHECK IF THE TOTAL FITS ANY OF THE AVAILABLE BOXES

        // dd($totalProductWidth);

        $resultsMessage = '';

        $productList = '';

        // IF THERE'S A USABLE BOX
        if (empty($usableBoxes) === false) {

            array_multisort(array_column($usableBoxes, 'volume_difference'), SORT_ASC, $usableBoxes);
            
            $boxToUse = (reset($usableBoxes)['box_name']);

            // echo "<b>Please use ".$recommendedBox."</b>";

            // dd(reset($usableBoxes));

            foreach ($inputtedProductsMaster as $indiv_product) {
                $productList = $productList."<li>".$indiv_product['product_name']."</li>";
            }

            // dd($productList);

            $resultsMessage = "<li>Please use <b>".$boxToUse."</b> for the following items:<ul>".$productList."</ul></li>";

            Session::flash('success', $resultsMessage);

            return view ('product_input');

        }
        // END OF IF THERE'S A USABLE BOX

        // IF THERE'S NO USABLE BOX, GET THE LARGEST PRODUCT AND TRY TO FIT IT IN A BOX, THEN PUT THE REST OF THE ORDERS IN A BOX

        $inputtedProductsAdjusted = $inputtedProductsMaster;

        $removedLargeProducts = array();

        // START THE FUNCTION HERE

        if (empty($usableBoxes) === true) {

            array_multisort(array_column($inputtedProductsAdjusted, 'product_volume_total'), SORT_DESC, $inputtedProductsAdjusted);

            $productToRemove = reset($inputtedProductsAdjusted);

            unset($inputtedProductsAdjusted[0]);

                // UPDATE THE TOTAL PRODUCT VOLUNE

            $updatedTotalProductVolume = $totalProductVolume - $productToRemove['product_volume_total'];

                // dd($updatedTotalProductVolume);

            $productToRemoveTotalVolume = $productToRemove['product_volume_total'];

                // CHECK IF THE REMOVED PRODUCT'S TOTAL VOLUME FITS A BOX

            $usableBoxesRemovedProduct = array();

            foreach ($boxOptions as $boxName => $boxAttributes) {

                    // dd($boxName.' => '.$boxAttributes['length']);

                    // GET THE TOTAL VOLUME OF THE BOX
                $totalBoxVolume = ($boxAttributes['length'] * $boxAttributes['width'] * $boxAttributes['height']);  

                    // GET THE DIFFERENCE BETWEEN THE BOX VOLUME AND REMOVED PRODUCT TO REMOVE VOLUME
                $updatedVolumeDifferenceRemovedProduct = ($totalBoxVolume - $productToRemoveTotalVolume);

                    // GET THE DIFFERENCE BETWEEN THE BOX VOLUME AND THE UPDATED TOTAL PRODUCT VOLUME
                $updatedVolumeDifferenceTotal = ($totalBoxVolume - $updatedTotalProductVolume);


                    // IF! THE BOX AND REMOVED PRODUCT VOLUME DIFFERENCE IS POSITIVE
                if ($updatedVolumeDifferenceRemovedProduct >= 0) {
                        // CREATE A NEW ARRAY OF USABLE BOXES WITH TOTAL VOLUME

                    $usableBoxesGetRemovedProduct = array($boxName => ['box_name' => $boxName,'length' => $boxAttributes['length'], 'width' => $boxAttributes['width'], 'height' => $boxAttributes['height'], 'total_product_weight' => $totalProductWeight, 'weight_limit' => $boxAttributes['weight_limit'], 'total_box_volume' => $totalBoxVolume, 'total_product_volume' => $productToRemoveTotalVolume, 'volume_difference' => $updatedVolumeDifferenceRemovedProduct]);

                    $usableBoxesRemovedProduct = array_merge($usableBoxesRemovedProduct, $usableBoxesGetRemovedProduct);

                        // END OF CREATE A NEW ARRAY OF USABLE BOXES WITH TOTAL VOLUME
                }
                    // END OF IF! THE BOX AND REMOVED PRODUCT VOLUME DIFFERENCE IS POSITIVE


                    // IF! THE BOX AND UPDATED TOTAL PRODUCT VOLUME DIFFERENCE IS POSITIVE, GET A BOX THAT FITS THE REST

                if ($updatedVolumeDifferenceTotal >= 0) {

                        // CREATE A NEW ARRAY OF USABLE BOXES WITH TOTAL VOLUME             

                    $usableBoxesGet = array($boxName => ['box_name' => $boxName,'length' => $boxAttributes['length'], 'width' => $boxAttributes['width'], 'height' => $boxAttributes['height'], 'total_product_weight' => $totalProductWeight, 'weight_limit' => $boxAttributes['weight_limit'], 'total_box_volume' => $totalBoxVolume, 'total_product_volume' => $updatedTotalProductVolume, 'volume_difference' => $updatedVolumeDifferenceTotal]);

                    $usableBoxes = array_merge($usableBoxes, $usableBoxesGet);

                        // END OF CREATE A NEW ARRAY OF USABLE BOXES WITH TOTAL VOLUME
                }

                    // END OF IF! THE BOX AND UPDATED TOTAL PRODUCT VOLUME DIFFERENCE IS POSITIVE, GET A BOX THAT FITS THE REST    
            }

                // END OF CHECK IF THE REMOVED PRODUCT'S TOTAL VOLUME FITS A BOX

            
                // IF NO BOX CAN FIT THE REMOVED PRODUCT
            if (empty($usableBoxesRemovedProduct) === true) {
                $resultsMessage = $resultsMessage."<li><b>Error:</b> No box can accommodate <i>".$productToRemove['product_name']." (Total product volume according to quantity also factored in)</i></li>";
                Session::flash('error', $resultsMessage);
                return view ('product_input');
            }
                // END OF IF NO BOX CAN FIT THE REMOVED PRODUCT

                // TELL WHICH BOX TO PUT THE LARGE ITEM IN

            array_multisort(array_column($usableBoxesRemovedProduct, 'volume_difference'), SORT_ASC, $usableBoxesRemovedProduct);

            $boxToUse = (reset($usableBoxesRemovedProduct)['box_name']);

            $productNameToRemove = "<li>".$productToRemove['product_name']."</li>";

            $resultsMessage = "<li class='mb-2'>Please use <b>".$boxToUse."</b> for:<ul>".$productNameToRemove."</ul></li>";

                // END OF TELL WHICH BOX TO PUT THE LARGE ITEM IN

            // TELL WHICH BOX TO PUT THE REST OF THE ITEMS IN
            if (empty($usableBoxes) === false) {

            array_multisort(array_column($usableBoxes, 'volume_difference'), SORT_ASC, $usableBoxes);

            $boxToUseTotal = (reset($usableBoxes)['box_name']);

            // dd($inputtedProductsAdjusted);

                foreach ($inputtedProductsAdjusted as $indiv_product) {
                    $productList = $productList."<li>".$indiv_product['product_name']."</li>";
                }

                $resultsMessage = $resultsMessage."<li>Please use <b>".$boxToUseTotal."</b> for the rest of the items:<ul>".$productList."</ul></li>";

            }


                // END OF TELL WHICH BOX TO PUT THE REST OF THE ITEMS IN

            // dd($usableBoxesRemovedProduct);

            if (empty($usableBoxes) === false) {

                Session::flash('success', $resultsMessage);

                return view ('product_input');

            }


        }

        $ticker = 1;

        $loopNum = $totalProductQuantity;

            function getNextLargeProduct ($inputtedProductsAdjusted, $totalProductVolume, $boxOptions, $totalProductWeight, $usableBoxes, $resultsMessage, $ticker, $updatedTotalProductVolume, $loopNum) {

                if ($ticker < $loopNum) {

                array_multisort(array_column($inputtedProductsAdjusted, 'product_volume_total'), SORT_DESC, $inputtedProductsAdjusted);

                $productToRemove = reset($inputtedProductsAdjusted);

                unset($inputtedProductsAdjusted[0]);

                // UPDATE THE TOTAL PRODUCT VOLUNE

                // $updatedTotalProductVolume = $totalProductVolume - $productToRemove['product_volume_total'];

                $updatedTotalProductVolume = $updatedTotalProductVolume - $productToRemove['product_volume_total'];

                // dd($updatedTotalProductVolume);

                $productToRemoveTotalVolume = $productToRemove['product_volume_total'];

                // CHECK IF THE REMOVED PRODUCT'S TOTAL VOLUME FITS A BOX

                $usableBoxesRemovedProduct = array();

                foreach ($boxOptions as $boxName => $boxAttributes) {

                    // dd($boxName.' => '.$boxAttributes['length']);

                    // GET THE TOTAL VOLUME OF THE BOX
                    $totalBoxVolume = ($boxAttributes['length'] * $boxAttributes['width'] * $boxAttributes['height']);  

                    // GET THE DIFFERENCE BETWEEN THE BOX VOLUME AND REMOVED PRODUCT TO REMOVE VOLUME
                    $updatedVolumeDifferenceRemovedProduct = ($totalBoxVolume - $productToRemoveTotalVolume);

                    // GET THE DIFFERENCE BETWEEN THE BOX VOLUME AND THE UPDATED TOTAL PRODUCT VOLUME
                    $updatedVolumeDifferenceTotal = ($totalBoxVolume - $updatedTotalProductVolume);


                    // IF! THE BOX AND REMOVED PRODUCT VOLUME DIFFERENCE IS POSITIVE
                    if ($updatedVolumeDifferenceRemovedProduct >= 0) {
                        // CREATE A NEW ARRAY OF USABLE BOXES WITH TOTAL VOLUME

                        $usableBoxesGetRemovedProduct = array($boxName => ['box_name' => $boxName,'length' => $boxAttributes['length'], 'width' => $boxAttributes['width'], 'height' => $boxAttributes['height'], 'total_product_weight' => $totalProductWeight, 'weight_limit' => $boxAttributes['weight_limit'], 'total_box_volume' => $totalBoxVolume, 'total_product_volume' => $productToRemoveTotalVolume, 'volume_difference' => $updatedVolumeDifferenceRemovedProduct]);

                        $usableBoxesRemovedProduct = array_merge($usableBoxesRemovedProduct, $usableBoxesGetRemovedProduct);

                        // END OF CREATE A NEW ARRAY OF USABLE BOXES WITH TOTAL VOLUME
                    }
                    // END OF IF! THE BOX AND REMOVED PRODUCT VOLUME DIFFERENCE IS POSITIVE


                    // IF! THE BOX AND UPDATED TOTAL PRODUCT VOLUME DIFFERENCE IS POSITIVE, GET A BOX THAT FITS THE REST

                    if ($updatedVolumeDifferenceTotal >= 0) {

                        // CREATE A NEW ARRAY OF USABLE BOXES WITH TOTAL VOLUME             

                        $usableBoxesGet = array($boxName => ['box_name' => $boxName,'length' => $boxAttributes['length'], 'width' => $boxAttributes['width'], 'height' => $boxAttributes['height'], 'total_product_weight' => $totalProductWeight, 'weight_limit' => $boxAttributes['weight_limit'], 'total_box_volume' => $totalBoxVolume, 'total_product_volume' => $updatedTotalProductVolume, 'volume_difference' => $updatedVolumeDifferenceTotal]);

                        $usableBoxes = array_merge($usableBoxes, $usableBoxesGet);

                        // END OF CREATE A NEW ARRAY OF USABLE BOXES WITH TOTAL VOLUME
                    }

                    // END OF IF! THE BOX AND UPDATED TOTAL PRODUCT VOLUME DIFFERENCE IS POSITIVE, GET A BOX THAT FITS THE REST    
                }

                // END OF CHECK IF THE REMOVED PRODUCT'S TOTAL VOLUME FITS A BOX

                // IF NO BOX CAN FIT THE REMOVED PRODUCT
                if (empty($usableBoxesRemovedProduct) === true) {
                    $resultsMessage = $resultsMessage."<li><b>Error:</b> No box can accommodate <i>".$productToRemove['product_name']." (Total product volume according to quantity also factored in)</i></li>";
                    Session::flash('error', $resultsMessage);
                    return view ('product_input');
                }
                // END OF IF NO BOX CAN FIT THE REMOVED PRODUCT

                // TELL WHICH BOX TO PUT THE LARGE ITEM IN

                array_multisort(array_column($usableBoxesRemovedProduct, 'volume_difference'), SORT_ASC, $usableBoxesRemovedProduct);

                $boxToUse = (reset($usableBoxesRemovedProduct)['box_name']);

                $productNameToRemove = "<li>".$productToRemove['product_name']."</li>";

                $resultsMessage = $resultsMessage."<li class='mb-2'>Please use <b>".$boxToUse."</b> for:<ul>".$productNameToRemove."</ul></li>";

                // END OF TELL WHICH BOX TO PUT THE LARGE ITEM IN

                // TELL WHICH BOX TO PUT THE REST OF THE ITEMS IN

                if (empty($usableBoxes) === false) {

                    array_multisort(array_column($usableBoxes, 'volume_difference'), SORT_ASC, $usableBoxes);

                    $boxToUseTotal = (reset($usableBoxes)['box_name']);

                    $resultsMessage = $resultsMessage."<li>Please use <b>".$boxToUseTotal."</b> for the rest of the items:</li>";

                }

                // dd($usableBoxes);

                if (empty($usableBoxes) === true) {

                    if ($ticker < 5) {

                        $ticker++;

                        getNextLargeProduct($inputtedProductsAdjusted, $totalProductVolume, $boxOptions, $totalProductWeight, $usableBoxes, $resultsMessage, $ticker, $updatedTotalProductVolume, $loopNum);

                    }

                }

                else if (empty($usableBoxes) === false) {

                    $restOfTheProducts = '';

                    foreach ($inputtedProductsAdjusted as $indiv_inputtedProductAdjusted) {
                        // dd($indiv_inputtedProductAdjusted['product_name']);
                        $restOfTheProducts = $restOfTheProducts."<li>".$indiv_inputtedProductAdjusted['product_name']."</li>";
                    }

                    $resultsMessage = $resultsMessage."<ul>".$restOfTheProducts."</ul>";

                        if (Session::has('resultsMessage')) {
                            Session::pull('resultsMessage');
                        }

                    Session::put('resultsMessage', $resultsMessage);


                }

              }

            }

        // IF A BOX THAT FITS THE REST CAN'T BE FOUND, REMOVE THE NEXT BIGGEST ITEM
        if (empty($usableBoxes) === true) {

            getNextLargeProduct($inputtedProductsAdjusted, $totalProductVolume, $boxOptions, $totalProductWeight, $usableBoxes, $resultsMessage, $ticker, $updatedTotalProductVolume, $loopNum);
            // dd('Second largest item needs to be allocated to a different box');
        }
        // END OF IF A BOX THAT FITS THE REST CAN'T BE FOUND, REMOVE THE NEXT BIGGEST ITEM  


        Session::flash('success', Session::get('resultsMessage'));

        return view ('product_input');


    }


}

