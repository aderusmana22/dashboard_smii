

jQuery('#world-map-markers').vectorMap(
{
    map: 'world_mill_en',
    backgroundColor: '#fff',
    borderColor: '#818181',
    borderOpacity: 0.25,
    borderWidth: 1,
    color: '#f4f3f0',
    regionStyle : {
        initial : {
          fill : '#B5B5C3'
        }
      },
     markerStyle: { initial: { r: 9, fill: "#019ff8", "fill-opacity": 0.9, stroke: "#fff", "stroke-width": 7, "stroke-opacity": 0.4 }, hover: { stroke: "#fff", "fill-opacity": 1, "stroke-width": 1.5 } },
    enableZoom: true,
    hoverColor: '#0a89c1',
    markers: [
		{ latLng: [44.5, -89.5], name: "Wisconsin" },
		{ latLng: [31.000000, -100.000000], name: "Texas" },
		{ latLng: [55.751244, 37.618423], name: "Rusia" },
		{ latLng: [51.507351, -0.127758], name: "London" },
		{ latLng: [43.93, 12.46], name: "San Marino" },
		{ latLng: [47.14, 9.52], name: "Liechtenstein" },
		
	],
    hoverOpacity: 0.7,
    normalizeFunction: 'linear',
    scaleColors: ['#b6d6ff', '#005ace'],
    selectedColor: '#c9dfaf',
    selectedRegions: [],
    showTooltip: true,
    onRegionClick: function(element, code, region)
    {
        var message = 'You clicked "'
            + region
            + '" which has the code: '
            + code.toUpperCase();

        alert(message);
    }
});


$('#india').vectorMap({
            map : 'in_mill',
            backgroundColor : 'transparent',
            regionStyle : {
                initial : {
                    fill : '#A1A5B7'
                }
            }
        });


$('#usa').vectorMap({
	map : 'us_aea_en',
	backgroundColor : 'transparent',
	regionStyle : {
		initial : {
			fill : '#7E8299'
		}
	}
});



$('#australia').vectorMap({
	map : 'au_mill',
	backgroundColor : 'transparent',
	regionStyle : {
		initial : {
			fill : '#5E6278'
		}
	}
});

 $('#uk').vectorMap({
	map : 'uk_mill_en',
	backgroundColor : 'transparent',
	regionStyle : {
		initial : {
			fill : '#3F4254'
		}
	}
});

       
        