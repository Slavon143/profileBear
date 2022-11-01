(function($){

    "use strict";

    // hide preloader and show page
    $(".preloader").fadeOut();
    $(".b2bking_dashboard_page_wrapper").delay(20).show();

    // draw chart
    drawSalesChart();

    $('#b2bking_dashboard_days_select').change(drawSalesChart);

    /*
    * Draw the Sales Chart
    */
    function drawSalesChart(){
        var selectValue = parseInt($('#b2bking_dashboard_days_select').val());
        $('#b2bking_dashboard_blue_button').text($('#b2bking_dashboard_days_select option:selected').text());

        if (selectValue === 0){
            $('.b2bking_total_b2b_sales_seven_days,.b2bking_total_b2b_sales_thirtyone_days, .b2bking_number_orders_seven, .b2bking_number_orders_thirtyone, .b2bking_number_customers_seven, .b2bking_number_customers_thirtyone, .b2bking_net_earnings_seven, .b2bking_net_earnings_thirtyone').css('display', 'none');
            $('.b2bking_total_b2b_sales_today, .b2bking_number_orders_today, .b2bking_number_customers_today, .b2bking_net_earnings_today').css('display', 'block');
        } else if (selectValue === 1){
            $('.b2bking_total_b2b_sales_today,.b2bking_total_b2b_sales_thirtyone_days, .b2bking_number_orders_today, .b2bking_number_orders_thirtyone, .b2bking_number_customers_today, .b2bking_number_customers_thirtyone, .b2bking_net_earnings_today, .b2bking_net_earnings_thirtyone').css('display', 'none');
            $('.b2bking_total_b2b_sales_seven_days, .b2bking_number_orders_seven, .b2bking_number_customers_seven, .b2bking_net_earnings_seven').css('display', 'block');
        } else if (selectValue === 2){
            $('.b2bking_total_b2b_sales_today,.b2bking_total_b2b_sales_seven_days, .b2bking_number_orders_today, .b2bking_number_orders_seven, .b2bking_number_customers_today, .b2bking_number_customers_seven, .b2bking_net_earnings_today, .b2bking_net_earnings_seven').css('display', 'none');
            $('.b2bking_total_b2b_sales_thirtyone_days, .b2bking_number_orders_thirtyone, .b2bking_number_customers_thirtyone, .b2bking_net_earnings_thirtyone').css('display', 'block');
        }

        if (selectValue === 0){
            // set label
            var labelsdraw = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];
            // set series
            var seriesdrawb2b = b2bking_dashboard.hours_sales_b2b.concat();
            var seriesdrawb2c = b2bking_dashboard.hours_sales_b2c.concat();

        } else if (selectValue === 1){
            // set label
            var date = new Date();
            var d = date.getDate();
            var labelsdraw = [d-6, d-5, d-4, d-3, d-2, d-1, d];
            labelsdraw.forEach(myFunction);
            function myFunction(item, index) {
              if (parseInt(item)<=0){
                let last = new Date();
                let month = last.getMonth()-1;
                let year = last.getFullYear();
                let lastMonthDays = new Date(year, month, 0).getDate();
                labelsdraw[index] = lastMonthDays+item;
              }
            }
            // set series
            var seriesdrawb2b = b2bking_dashboard.days_sales_b2b.concat();
            var seriesdrawb2c = b2bking_dashboard.days_sales_b2c.concat();
            seriesdrawb2b.splice(7,24);
            seriesdrawb2c.splice(7,24);
            seriesdrawb2b.reverse();
            seriesdrawb2c.reverse();
        } else if (selectValue === 2){
            // set label
            var labelsdraw = [];
            let i = 0;
            while (i<32){
                let now = new Date();
                let pastDate = new Date(now.setDate(now.getDate() - i));
                let day = pastDate.getDate();
                labelsdraw.unshift(day);
                i++;
            }
            // set series
            var seriesdrawb2b = b2bking_dashboard.days_sales_b2b.concat();
            var seriesdrawb2c = b2bking_dashboard.days_sales_b2c.concat();
            seriesdrawb2b.reverse();
            seriesdrawb2c.reverse();
        }

        var chart = new Chartist.Line('.campaign', {
            labels: labelsdraw,
            series: [
                seriesdrawb2b,
                seriesdrawb2c
            ]
        }, {
            low: 0,
            high: Math.max(seriesdrawb2c, seriesdrawb2b),

            showArea: true,
            fullWidth: true,
            plugins: [
                Chartist.plugins.tooltip()
            ],
            axisY: {
                onlyInteger: true,
                scaleMinSpace: 40,
                offset: 55,
                labelInterpolationFnc: function(value) {
                    return b2bking_dashboard.currency_symbol + (value / 1);
                }
            },
        });

        // Offset x1 a tiny amount so that the straight stroke gets a bounding box
        // Straight lines don't get a bounding box 
        // Last remark on -> http://www.w3.org/TR/SVG11/coords.html#ObjectBoundingBox
        chart.on('draw', function(ctx) {
            if (ctx.type === 'area') {
                ctx.element.attr({
                    x1: ctx.x1 + 0.001
                });
            }
        });

        // Create the gradient definition on created event (always after chart re-render)
        chart.on('created', function(ctx) {
            var defs = ctx.svg.elem('defs');
            defs.elem('linearGradient', {
                id: 'gradient',
                x1: 0,
                y1: 1,
                x2: 0,
                y2: 0
            }).elem('stop', {
                offset: 0,
                'stop-color': 'rgba(255, 255, 255, 1)'
            }).parent().elem('stop', {
                offset: 1,
                'stop-color': 'rgba(64, 196, 255, 1)'
            });
        });

        var chart = [chart];
    }
    
})(jQuery);