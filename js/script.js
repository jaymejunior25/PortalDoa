$(document).ready(function() {
    $("#sidebar-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });
});

function loadPage(page) {
    $("#content").load(page + ".php");
}
