<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <!--<base href="https://www.optimumlinkup.com.ng/pos/">--><base href=".">
        <link rel="icon" href="/img/favicon.ico" type="image/x-icon">
        

                   
                   
                    <link href="css/custom.css" rel="stylesheet" rev="stylesheet" type="text/css" media="all">
                   
                    
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body><div class="row">
        <div class="widget-box">
            <div class="widget-content">
                <div id="chartdiv" style="overflow: hidden; text-align: left;"><div class="amcharts-main-div" style="position: relative;"></div></div>
            </div>
        </div>
    </div>

</div>
<div id="footer" class="col-md-12 hidden-print">
	
</div>

</div><!--end #content-->
<!--end #wrapper-->

<script src="./dashboard_files/amcharts.js.download"></script>
<script src="./dashboard_files/serial.js.download"></script>

<script src="./dashboard_files/canvasjs.min.js.download"></script>

<script language="javascript">
	<!-- amCharts javascript code -->
		
			AmCharts.makeChart("chartdiv",
				{
					
					
					"theme": "light",
					"marginTop": 50,
					"marginRight": 40,
			
					"type": "serial",
					"categoryField": "category",
					"autoMarginOffset": 40,
					"marginRight": 60,
					"marginTop": 60,
					"plotAreaBorderAlpha": 0.16,
					"plotAreaFillAlphas": 0.36,
					"startDuration": 1,
					"fontSize": 13,
					
					"categoryAxis": {
						"gridPosition": "start"
					},
					
					
					"chartCursor": {
//                "categoryBalloonDateFormat": "YYYY",
                "cursorAlpha": 0,
                "valueLineEnabled": true,
                "valueLineBalloonEnabled": true,
                "valueLineAlpha": 0.5,
                "fullWidth": true,
                "color":"#fff",
                "cursorColor": "#4e7d2a",
                "zoomable": false
            },
			
			
					
					"trendLines": [],
					"graphs": [
						{
							"lineColor": "#4e7d2a",
							"balloonText": "[[title]] of [[category]]:[[value]]",
							"bullet": "round",
							"bulletSize": 10,
							"id": "AmGraph-1",
							"lineThickness": 3,
							"title": "Dashboard Chart",
							"type": "smoothedLine",
							"valueField": "column-1"
						}
					],
					"guides": [],
					"valueAxes": [{
                    "axisAlpha": 0.5,
                    "position": "left"
                }],
					"allLabels": [],
					"balloon": {},
					"titles": [
						{
							"alpha": 100,
							"id": "Chart of Dashboard",
							"size": 14,
							"text": "Chart of Statistics",
							"color": '#4e7d2a'
						}
					],
					"dataProvider": [
						{
							"category": "Total Test",
							"column-1": "50"
						},
						{
							"category": "Total Test Awaiting Result",
							"column-1": "50"
						},
						{
							"category": "Ready for Printing",
							"column-1": "78"
						},
						
						{
							"category": "Awaiting Pathologist Result",
							"column-1": "56"
						},
						{
							"category": "Rejected",
							"column-1": "67"
						}
					]
				}
			);
		</script>



<!--<div id="chartdiv" style="width: 100%; height: 400px; background-color: #FFFFFF;" ></div>-->

<script>
    $(function () {
//        $('#sel_location_modal').slideDown('slow');
            showSelectLocationModal();


        var chart = AmCharts.makeChart("chartdiv", {
            "titles": [{
                    "text": "Chart Of",
                    "size": 15,
                    "color": '#4e7d2a'
                }],
            "type": "serial",
            "theme": "light",
            "marginTop": 50,
            "marginRight": 40,
            "dataProvider": [{
                    "index": "Total Test",
                    "value": 100                }, {
                    "index": "Total Test Awaiting Result",
                    "value": 50                }, {
                    "index": "Awaiting Approval",
                    "value": 30               }, {
                    "index": "Awaiting Pathologist Result",
                    "value": 8                }],
            "valueAxes": [{
                    "axisAlpha": 0.5,
                    "position": "left"
                }],
            "graphs": [{
                    "id": "g1",
                    "balloonText": "[[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
                    "bullet": "round",
                    "bulletSize": 8,
                    "lineColor": "#4e7d2a",
                    "lineThickness": 2,
                    "negativeLineColor": "#4e7d2a",
                    "type": "smoothedLine",
                    "valueField": "value",
                    "balloonColor": "#f7941d",
                    "balloon": {
                        "adjustBorderColor": true,
                        "color": "#fff",
                        "cornerRadius": 5,
                        "fillColor": "#f7941d"
                    }
                }],
//            "chartScrollbar": {
//                "graph": "g1",
//                "gridAlpha": 0,
//                "color": "#888888",
//                "scrollbarHeight": 55,
//                "backgroundAlpha": 0,
//                "selectedBackgroundAlpha": 0.1,
//                "selectedBackgroundColor": "#888888",
//                "graphFillAlpha": 0,
//                "autoGridCount": true,
//                "selectedGraphFillAlpha": 0,
//                "graphLineAlpha": 0.2,
//                "graphLineColor": "#c2c2c2",
//                "selectedGraphLineColor": "#888888",
//                "selectedGraphLineAlpha": 1
//
//            },
            "chartCursor": {
//                "categoryBalloonDateFormat": "YYYY",
                "cursorAlpha": 0,
                "valueLineEnabled": true,
                "valueLineBalloonEnabled": true,
                "valueLineAlpha": 0.5,
                "fullWidth": true,
                "color":"#fff",
                "cursorColor": "#4e7d2a",
                "zoomable": false
            },
//            "dataDateFormat": "YYYY",
            "categoryField": "index",
            "categoryAxis": {
//                "minPeriod": "YYYY",
                "parseDates": false,
                "minorGridAlpha": 0.1,
                "minorGridEnabled": true,
                "autoWrap":true,
            },
            "export": {
                "enabled": true
            }
        });
//
//        chart.addListener("rendered", zoomChart);
//        if (chart.zoomChart) {
//            chart.zoomChart();
//        }
//
//        function zoomChart() {
//            chart.zoomToIndexes(Math.round(chart.dataProvider.length * 0.4), Math.round(chart.dataProvider.length * 0.55));
//        }


    });
</script>
</body>
</html>