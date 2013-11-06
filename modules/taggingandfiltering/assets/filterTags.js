var tagAndFilters = {
    addRemoveTag:function() {
        var idLang;
        $('.addTag').click(function() {
            idLang = $(this).attr('langId');
            return !$('#selectFilterTags_'+idLang+' option:selected').remove().appendTo('#selectedFilterTags_'+idLang);
        });

        $('.removeTag').click(function() {
            idLang = $(this).attr('langId');
            return !$('#selectedFilterTags_'+idLang+' option:selected').remove().appendTo('#selectFilterTags_'+idLang);
        });

        $('#filter').submit(function() {
            $('.selectedFilterTags option').each(function(i) {
                $(this).attr("selected", "selected");
            });
        });
    },
    addNewFilter:function() {
        $('#AddNewFilter').click(function() {
            $.ajax({
			type: 'GET',
			url: '../modules/taggingandfiltering/productTagsFilter.php',
			async: true,
			cache: false,
			dataType : "json",
			data: 'AddNewFilter=true&name='+$('#filterName').val()+'&id_lang='+$('#id_lang').val(),
			success: function(jsonData)
			{
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				alert("TECHNICAL ERROR: unable to add filter to database.\n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
			}
		});

        });
    },
    showAddFilterForm:function() {
        $('#showForm').click(function() {
            $('.addNewFilter').toggle();
        });
    }

}

//when document is loaded...
$(document).ready(function() {
    tagAndFilters.addRemoveTag();
    tagAndFilters.addNewFilter();
    tagAndFilters.showAddFilterForm();
});