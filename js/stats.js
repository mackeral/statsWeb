$(document).ready(function(){
    // light up the nav for this page
    var thisPage = location.pathname.substring(location.pathname.lastIndexOf('/')+1);
    var li = $("a[href='/stats/" + thisPage + "']").parent('li');
    $(li).addClass('active');
});
function escapedID(myid) {
    return "#" + myid.replace( /(:|\.|\[|\]|\/)/g, "\\\\$1" );
}