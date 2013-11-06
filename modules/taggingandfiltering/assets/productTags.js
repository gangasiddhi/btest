$('#AddNewTag').click(function() {
    $.ajax({
        type: 'GET',
        url:  baseDir+'productTagsFilter.php',
        cache: false,
        dataType: "json",
        data:"AddNewTag=1&id_lang="+$('#id_lang').val()+"&name="+$('#tag_name').val(),
        success: function(jsonData)
        {
            if(jsonData.hasError)
            {
                alert(jsonData.hasError);
            }
            if(jsonData.tagId)
            {  
                var string = "<option value="+jsonData.tagId +">"+jsonData.tagName+"</option>";
                $('#selectProductTags').append(string);
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown)
        {          
            alert(errorThrown);
        }
    }); 
    $(".tag_empty").val('');
}); 

$('#addTag').click(function() {
    return !$('#selectProductTags option:selected').remove().appendTo('#selectedProductTags');
});

$('#removeTag').click(function() {
    return !$('#selectedProductTags option:selected').remove().appendTo('#selectProductTags');
});
$('#product').submit(function() {
    $('#selectedProductTags option').each(function(i) {
            $(this).attr("selected", "selected");
    });
});