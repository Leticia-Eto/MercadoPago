$('a').on('click', function(e){
    e.preventDefault();
    let vl   = $("#valor").val();
    let link = $(this).attr('href');
    location.href= link + '?vl='+vl;
});
