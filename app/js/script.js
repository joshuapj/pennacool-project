$(document).ready(function () {
    $("form").submit(function (e) {
        if ($("#name").val() === "") {
            alert("Name is required!");
            e.preventDefault();
        }
    });
});