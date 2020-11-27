<?php 

 /*****CODE TO FETCH DATA FROM SAGE API*****/

  $requestUrl="API_URL";	 			// USE YOUR API URL HERE		
	$request = curl_init($requestUrl); 
	curl_setopt($request, CURLOPT_HEADER, 0);
	curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); 
	curl_setopt($request, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Accept:*/*']);
	curl_setopt($request, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);						
	$response = curl_exec($request); 
	curl_close($request); 
	$json_data=json_decode($response,true);

  $qwerty[] = $json_data;

  foreach ($qwerty as $val)
			{
				$data = $val['result'];
				foreach($data as $var){
					$code[] = $var['productCode'];
					$code[] = $var['quantityInStock'];
				  $all_data[] = array($var['productCode'],$var['quantityInStock']);
				}
	}


 /*******CODE TO UPDATE MAGENTO**********/

$userData = array("username" => "USERNAME", "password" => "PASSWORD");              //MAGENTO API DETAILS
$ch = curl_init("WEBSITE_URL/rest/V1/integration/admin/token");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Lenght: " . strlen(json_encode($userData))));
$token = curl_exec($ch);
$token=  json_decode($token);



$productUrl='WEBSITE_URL/rest/V1/products?fields=items[id,name,sku,status]&searchCriteria[page_size]=2000';
$ch = curl_init($productUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Authorization: Bearer $token"));
$productList = curl_exec($ch);
$err = curl_error($ch);
$products = json_decode($productList, true);
curl_close($ch);

$magenpro  = $products['items'];
foreach ($magenpro as $productm) {
	 $id = $productm['id'];
   $skurm = $productm['sku'];
	 $removeslash = str_replace('/','', $skurm);
	 $skum = str_replace(' ','', $removeslash);
	 $magdat[$skum] = $id;
	 $i++;
}
$skus = $all_data;

foreach ($skus as $sku) {
	$sku_sage = $sku[0];
	$skuv = $magdat[$sku_sage];
	$stock = $sku[1];
     file_get_contents("WEBSITE_URL/updateqty.php?pro_id=$skuv&qty=$stock");
}
echo "Stock has been updated";

