(function(window) {

    'use strict';

    var $ = window.jQuery;

    function resetSupplierPurchaseForm() {
        $('#supplier-purchase-form').attr('action', currentUrl);
        $('.supplier-select-box').attr('selectedIndex', 0);
        $('.supplier-purchase-form-inner-row.first input').each(function(i, e) {
            $(e).val('');
        });
        $('.supplier-purchase-form-inner-row:not(.first)').each(function(i, e) {
            $(e).remove();
        });
    }

    $(function() {
        $("#supplier-purchase-pagination").pagination(numberOfPurchases, {
            prev_text: prevText,
            next_text: nextText,
            items_per_page: itemPerPage,
            num_display_entries: 10,
            num_edge_entries: 2,
            current_page : page - 1,
            callback: function(pageNo, $pagination) {
                if (pageNo == page - 1) {
                    return;
                }

                var urlParams =  getQueryString();

                urlParams["page"] = parseInt(pageNo, 10) + 1;

                goToUrl(location.pathname + "?" + $.param(urlParams));

                return false;
            }
        });
    });

    $('.add-new-supplier-purchase').live('click', function(e) {
        $('#supplier-purchase-container.hidden').slideDown();

        resetSupplierPurchaseForm();

        return false;
    });

    $('.supplier-purchase-form-add-new-inner-row').live('click', function(e) {
        var innerRow = $('<div>').addClass('supplier-purchase-form-inner-row');
        var txtReference = $('<input>')
            .addClass('product-reference')
            .attr({
                type: 'text',
                placeholder: referencePlaceholder
            });
        var txtQuantity = $('<input>')
            .addClass('product-quantity')
            .attr({
                type: 'text',
                placeholder: quantityPlaceholder
            });
        var lnkAddNew = $('<a>')
            .addClass('supplier-purchase-form-add-new-inner-row')
            .attr('href', '#')
            .append(
                $('<img>').attr('src', '../img/admin/add.gif')
            );
        var lnkDelExisting = $('<a>')
            .addClass('supplier-purchase-form-delete-inner-row')
            .attr('href', '#')
            .append(
                $('<img>').attr('src', '../img/admin/forbbiden.gif')
            );

        innerRow
            .append(txtReference)
            .append(' - ')
            .append(txtQuantity)
            .append(' ')
            .append(lnkAddNew)
            .append(' ')
            .append(lnkDelExisting)
            .appendTo('#supplier-purchase-form .fields');

        return false;
    });

    $('.supplier-purchase-form-delete-inner-row').live('click', function(e) {
        $(this).parents('.supplier-purchase-form-inner-row:not(.first)').remove();

        return false;
    });

    $('#supplier-purchase-form').live('submit', function(e) {
        var data = {};
        var container = $('.supplier-purchase-form-inner-row');

        container.each(function(i, o) {
            var reference = $(o).find('.product-reference').val();
            var quantity = $(o).find('.product-quantity').val();

            if (! (reference && quantity)) {
                return true;
            }

            data[reference] = quantity;
        });

        // serializing to hidden field..
        $('#supplier-purchase-form-data').val(JSON.stringify(data));

        if ($.isEmptyObject(data)) {
            alert(errProductFields);
            return false;
        }
    });

})(this);
