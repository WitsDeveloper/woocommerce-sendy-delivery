// JavaScript Document
jQuery(document).ready(function ($) {
	
	var billing_country = $("#billing_country").val();
	//var billingcountry = (typeof billing_country === 'undefined') ? toLowerCase(billing_country) : 'ke';
	
	
	$("#billing_address_1").geocomplete({
	  country: billing_country,
	  details: "form",
	  detailsAttribute: "billing-data-geo",	  
	  types: ["geocode", "establishment"],
	});
	
	var shipping_country = $("#shipping_country").val();
	//var shipping_country = (typeof shipping_country === 'undefined') ? toLowerCase(shipping_country) : 'ke';
	
	
	$("#shipping_address_1").geocomplete({
	  country: shipping_country,
	  details: "form",
	  detailsAttribute: "shipping-data-geo",	  
	  types: ["geocode", "establishment"],
	});
	
});
