var map,ristmik;
var ristmik_lon = 667857;
var ristmik_lat = 6440815;
var lon = 667646;
var lat = 6440357;
var zoom = 8;

var directions = new google.maps.DirectionsService();
var gproj = new OpenLayers.Projection("EPSG:4326");
var layers = {
	kaart: new OpenLayers.Layer.WMS("Eesti kaart","http://kaart.maaamet.ee/wms/kaart?",{layers:'CORINE,BAASKAART,KAART24,HALDUSPIIRID,TEED,KYLAD',format:'image/png'},{singleTile:false,gutter:40}),
	results: new OpenLayers.Layer.Vector('Results',{
		styleMap: new OpenLayers.StyleMap({
			"default": new OpenLayers.Style({
				strokeColor: "#6a5acd",
				strokeOpacity: 0.6,
                strokeWidth: 4,
                externalGraphic: "http://www.gebweb.net/optimap/icons/icong.png",
	            graphicWidth: 20,
	            graphicHeight: 34,
	            graphicXOffset: -10,
	            graphicYOffset: -34
			})
		})
	}),
	markers: new OpenLayers.Layer.Vector('Markers',{
		styleMap: new OpenLayers.StyleMap({
			"default": new OpenLayers.Style({
				strokeColor: "#6a5acd",
				strokeOpacity: 0.6,
                strokeWidth: 4,
                externalGraphic: "http://www.gebweb.net/optimap/icons/iconr.png",
	            graphicWidth: 20,
	            graphicHeight: 34,
	            graphicXOffset: -10,
	            graphicYOffset: -34
			})
		})
	})
};

var options = {
	maxExtent: new OpenLayers.Bounds(365000,6308000,749000,6692000),
	restrictedExtent: new OpenLayers.Bounds(365000,6308000,749000,6692000),
	resolutions: [750,375,187.5,93.75,46.875,23.4375,11.71875,5.859375,2.9296875,1.46484375,0.732421875],
	units: 'm',
	projection: new OpenLayers.Projection("EPSG:3301")
};

var json = {
	"type":"FeatureCollection",
	"features":[
		{
			"type":"Feature",
			"id":"OpenLayers.Feature.Vector_1463",
			"properties":{},
			"geometry":{
				"type":"LineString",
				"coordinates":[[667854.09327595,6440813.3880099],[667789.64015095,6440770.1751193],[667730.31397908,6440734.2864474],[667694.4253072,6440718.1731662],[667635.8315572,6440693.2708224],[667555.26515095,6440663.2415256],[667517.17921345,6440648.5930881],[667471.03663533,6440630.2825412],[667434.41554158,6440611.2395724],[667404.3862447,6440592.9290256],[667373.62452595,6440571.6887912],[667399.99171345,6440540.9270724],[667421.23194783,6440528.4759006],[667452.72608845,6440504.3059787],[667476.89601033,6440479.4036349],[667511.31983845,6440452.3040256],[667537.68702595,6440437.6555881],[667558.92726033,6440429.5989474],[667587.49171345,6440416.4153537],[667624.84522908,6440394.4426974],[667630.70460408,6440386.3860568],[667628.50733845,6440348.3001193]]
			}
		},{
			"type":"Feature",
			"id":"OpenLayers.Feature.Vector_1469",
			"properties":{},
			"geometry":{
				"type":"Point",
				"coordinates":[667646.08546345,6440357.8216037]
			}
		}
	]
};

// label over
$.fn.labelOver = function(overClass) {
	return this.each(function(){
		var label = jQuery(this);
		var f = label.attr('for');
		if (f) {
			var input = jQuery('#' + f);
			this.hide = function() {
			  label.css({ textIndent: -10000 })
			  //label.css('top','10000');
			}
			this.show = function() {
			  if (input.val() == '') label.css({ textIndent: 0 })
			  //if (input.val() == '') label.css('margin-left','10');
			}
			// handlers
			input.focus(this.hide);
			input.blur(this.show);
		  	label.addClass(overClass).click(function(){ input.focus() });

			if (input.val() != '') this.hide();
		}
	});
}

//random string
function rnd(){ return String((new Date()).getTime()).replace(/\D/gi,''); }

function getDirections(start,end){
	var pointList = [];
	var distance = 0;
	var duration = 0;
	layers.results.removeAllFeatures();
	var request = {
		origin:start,
		destination:end,
		travelMode: google.maps.TravelMode.DRIVING
	};
	directions.route(request, function(result, status) {
		if (status == google.maps.DirectionsStatus.OK) {
			//var path = result.routes[0].path;
			$(result.routes).each(function(i,route){
				$(route.legs).each(function(j,leg){
					// distance
					distance += leg.distance.value;
					// time
					duration += leg.duration.value;
					// coordinates
					$(leg.steps).each(function(k,step){
						$(step.path).each(function(l, point) {
							point = /\(([-.\d]*), ([-.\d]*)/.exec(point);
							if (point) { 
								var lat = parseFloat(point[1]);
								var lon = parseFloat(point[2]);
								pointList.push(new OpenLayers.Geometry.Point(lon,lat).transform(gproj, map.getProjectionObject()));
							}
					    });
					});
				});
			});
			
			lineFeature = new OpenLayers.Feature.Vector(new OpenLayers.Geometry.LineString(pointList),null);
			startPoint = new OpenLayers.Feature.Vector(pointList[0],null);	
			layers.results.addFeatures([lineFeature,startPoint]);
			map.zoomToExtent(layers.results.getDataExtent());
			$('#pikkus').html(Math.round(distance/1000));
			$('#aeg').html(Math.round(duration/3600*10)/10);
			$('#searchresult').show();
		} else
			$('#searchfailed').show();
	});
}

function resetSearch(){
    $('.search').hide();
    $('.start').html('');
    $('#addressStr').val('');
    layers.results.removeAllFeatures();
    $('#searchbox').show();
    $('label').trigger('click');
}

function clickedAddAddress() {
	if($('#addressStr').val()!=''){
		$('.search').hide();
		if($('#addressStr').val().length>10){
			s = $('#addressStr').val().substring(0, 7);
			s += '...';
		} else {
			s = $('#addressStr').val();
		}
		
		$('.start').html(s);
		getDirections($('#addressStr').val()+', Estonia',ristmik);
	}
	return false;
}

function postLogin(){
	var p = $('#Password').val();
	if(p!=''){
		//check the username exists or not from ajax
		$.post(reqURL+'request.php?'+rnd(),{
				c:'login',
				m:'setLogin',
				Password:p
			},
			function(response){
				if(response.status=='1'){ //if correct login detail
					window.location.reload();
				}
				else {
					$('#Password').val('');
				}
			},
			'json'
		);
	}
}

function init(){
	map = new OpenLayers.Map('mapbox',options);
	for(var key in layers) {
		map.addLayer(layers[key]);
	}
	map.setCenter(new OpenLayers.LonLat(lon,lat),zoom);
	
	var np = new OpenLayers.Geometry.Point(ristmik_lon,ristmik_lat).transform(map.getProjectionObject(),gproj);
	ristmik = new google.maps.LatLng(np.y,np.x);
	
	var geojson_format = new OpenLayers.Format.GeoJSON();
	layers.markers.addFeatures(geojson_format.read(json));
}

$(document).ready(function(){
	if($.browser.msie && $.browser.version == '7.0'){}
	
	$('#Password').keypress(function(e){
		var KeyID = (window.event) ? event.keyCode : e.keyCode;
		if(KeyID == 13 || KeyID == 9){
			postLogin();
		}
	});
	$('#goin').click(function(e){
		e.preventDefault();
		postLogin();
    });
    $('#openlogin').click(function(e){
    	e.preventDefault();
    	$(this).hide();
    	$('#login').show();
    });
    
    //labels over inputs
	//$(this).bind('click', function() {
		$('label').labelOver('over');
	//});
    
});