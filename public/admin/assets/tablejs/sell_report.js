$('#most_sell_report').DataTable( {
    order: false,
    "oLanguage": {
        "sEmptyTable": "Sell report Available",
        "sZeroRecords": "Sell report Available",
    }
});


    $(document).on('click','#download',function(){
        var url = $('#url').val();
        // var from_date = $('#from_date').val() ;
        // var to_date = $('#to_date').val();
        // var order_status = $('#order_status').val();   
        $.ajax({
            type : 'POST',
            url  : url+"order/generate_most_sell_report",
            // data : {from_date : from_date, to_date : to_date ,order_status : order_status},
            dataType : 'json'
        }).done(function(data){
            var $a = $("<a>");
            $a.attr("href",data.file);
            $a.attr("download", data.filename);
            $("body").append($a);
            $a[0].click();
            $a.remove();
        });
    })