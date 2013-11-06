$(document).ready(function() {    
    var months = new Array(12);
    months[0] = "Ocak";
    months[1] = "Şubat";
    months[2] = "Mart";
    months[3] = "Nisan";
    months[4] = "Mayıs";
    months[5] = "Haziran";
    months[6] = "Temmuz";
    months[7] = "Ağustos";
    months[8] = "Eylül";
    months[9] = "Ekim";
    months[10] = "Kasım";
    months[11] = "Aralık";

    var current_date = new Date();
    month_value = current_date.getMonth();
    day_value = current_date.getDate();
    year_value = current_date.getFullYear();
    fullDate=day_value + " " +months[month_value] + " " + year_value;

    // Load the theme
    Galleria.loadTheme(theme_path);
    
    
//    Galleria.run('#galleria', {
//        dataSource: "#source",
//        keepSource: true // this prevents galleria from clearing the data source container
//    });
    // Initialize Galleria
    $('#galleria').galleria();
});
