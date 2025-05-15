$(function(){

      $('#my_calendar_calSize').rescalendar({
          id: 'my_calendar_calSize',
          jumpSize: 5,
          calSize: 10,
          data: [
              {
                  id: 1,
                  name: 'item1',
                  startDate: '2019-03-01',
                  endDate: '2019-03-03',
                  customClass: 'greenClass'
              },
              {
                  id: 2,
                  name: 'item2',
                  startDate: '2019-03-05',
                  endDate: '2019-03-15',
                  customClass: 'blueClass',
                  title: 'Title 2 en'
              },
              {
                  id: 3,
                  name: 'item5',
                  startDate: '2019-03-05',
                  endDate: '2019-03-08',
                  customClass: 'greenClass'
              }
          ],

          dataKeyField: '',
          dataKeyValues: ['']
      });
  });

const month = ["January","February","March","April","May","June","July","August","September","October","November","December"];

const d = new Date();
let name = month[d.getMonth()];
document.getElementById("month").innerHTML = name;

var today = new Date();
var dd = today.getDate();
if (dd < 10) {
  dd = "0" + dd;
}