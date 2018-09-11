function jsonTable(){
   var title = {
          text: ''   
      };
      var subtitle = {
           text: ''
      };
      var xAxis = {
          categories: ['01', '02', '03', '04', '05', '06'
                 ,'07', '08', '09', '10', '11', '12','13'
                 ,'14', '15', '16', '17', '18','19', '20'
                 , '21', '22', '23', '24','25', '26', '27'
                 , '28', '29', '30','31']
      };
      var yAxis = {
         title: {
            text: ''
         },
         /*plotLines: [{
            value: 0,
            width: 1,
            color: '#808080',
         }]*/

         tickPositions: yaxis,
      };

      var tooltip = {
         valueSuffix: ''
      }

      var legend = {
         layout: 'vertical',
         align: 'right',
         verticalAlign: 'middle',
         borderWidth: 0
      };

     
      
      Highcharts.setOptions({  
         colors: ['#F56678','#23DA20']  
      }); 
      
      var json = {};

      json.title = title;
      json.subtitle = subtitle;
      json.xAxis = xAxis;
      json.yAxis = yAxis;
      json.tooltip = tooltip;
      json.legend = legend;
      json.series = series;


   $('#jsonTable').highcharts(json);

}