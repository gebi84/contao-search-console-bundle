!function ($) {
    "use strict";

    $(function () {
        //add search bar
        $('#tl_navigation').find('ul:first').before($('<div id="search_box_container">' +
            '<form action="_contao-search-console/result" method="post">' +
            '<input placeholder="search|cmd" type="text" id="search_console" name="search" value="" />' +
            '<input type="hidden" name="REQUEST_TOKEN" id="search_console_request_token" value="' + Contao.request_token + '" />' +
            '</form>' +
            '</div>'));

        //register focus
        let activeFocus = $(document.activeElement);
        if (!activeFocus || activeFocus.get(0).id === 'top') {
            $('#search_console').focus();
        }

        $.widget("custom.catcomplete", $.ui.autocomplete, {
            _create: function () {
                this._super();
                this.widget().menu("option", "items", "> :not(.ui-autocomplete-category)");
            },
            _renderMenu: function (ul, items) {
                var that = this,
                    currentCategory = "";
                $.each(items, function (index, item) {
                    var li;
                    if (item.category != currentCategory) {
                        ul.append("<li class='ui-autocomplete-category'>" + item.category + "</li>");
                        currentCategory = item.category;
                    }
                    li = that._renderItemData(ul, item);
                    if (item.category) {
                        li.attr("aria-label", item.category + " : " + item.label);
                    }
                });
            }
        });

        function split(val) {
            return val.split(/ \s*/);
        }

        $("#search_console").catcomplete({
            source: function (request, response) {
                $.ajax({
                    url: "_contao-search-console/search",
                    data: {
                        search: request.term,
                        REQUEST_TOKEN: $('#search_console_request_token').val()
                    },
                    success: function (data, status, xhr) {

                        //redirect if not loggind
                        if (data.redirect) {
                            self.location.href = data.redirect;
                        }

                        if (data.resultHtml) {
                            $('#main').html(data.resultHtml);
                        }

                        response(data.items);
                    }
                });
            },
            minLength: 0,
            noCache: true,
            focus: function () {
                // prevent value inserted on focus
                return false;
            },
            select: function (event, ui) {

                if (ui.item.action) {
                    if (ui.item.action === 'redirect') {
                        self.location.href = ui.item.url;
                        return false;
                    }
                }

                var terms = split(this.value);

                // remove the current input
                terms.pop();

                // add the selected item
                terms.push(ui.item.value);
                // add placeholder to get the comma-and-space at the end
                terms.push("");
                this.value = terms.join(" ");
                this.value = this.value.substring(0, this.value.length - 1);
                return false;
            }
        });

    });
}(jQuery);