$(function () {
    $(".ita-box-add-button").each(function () {
        $(this).click(function () {
            $('.ita-datepicker').each(function () {
                $(this).datepicker("destroy");
            });

            var incId = parseInt($("div.ita-box-raccolta:last").attr("id").substring(12, 14)) + 1;

            var $nuovaRaccolta = $(".ita-div-box-template").eq(0).clone().removeClass("ita-div-box-template");
            $nuovaRaccolta.find("div.ita-header-raccolta:last").text(incId);

            if (incId.toString().length == 1) {
                incId = "0" + incId;
            }

            var delButton = "<button id=\"delButtonBox_" + incId + "\" name=\"delButtonBox[" + incId + "]\" class=\"ita-del-box italsoft-button italsoft-button--circled\" title=\"Elimina Raccolta\" type=\"button\">";
            delButton += "<i class=\"icon ion-minus italsoft-icon\"></i>";
            delButton += "</button>";

            $nuovaRaccolta.find(".italsoft-raccolta-actions").append(delButton);
            
            $nuovaRaccolta.find("#delButtonBox_" + incId).click(function () {
                $("#boxRaccolta_" + incId).remove();
                return;
            });

            $nuovaRaccolta.find("div.ita-header-raccolta:last").attr("id", "headerRaccolta_" + incId);
            $nuovaRaccolta.attr("id", "boxRaccolta_" + incId);
            $nuovaRaccolta.attr("name", "boxRaccolta_" + incId);
            $nuovaRaccolta.find("input.ita-datepicker").mask("99/99/9999");

            $nuovaRaccolta.find("input, select, textarea").each(function () {
                var $el = $(this),
                    defaultValue = $el.data('default');

                if (!this.id && !this.name) {
                    return;
                }

                $el.attr('autocomplete', 'off');

                if ($el.attr("type") == 'checkbox') {
                    if (!$el.is('[readonly]')) {
                        if (defaultValue && defaultValue == $el.attr('value')) {
                            $el.attr('checked', 'checked');
                        } else {
                            $el.removeAttr('checked');
                        }
                    }
                } else if ($el.attr("type") == 'radio') {
                    if (!$el.is('[readonly]')) {
                        if (defaultValue && defaultValue == $el.attr('value')) {
                            $el.attr('checked', 'checked');
                        } else {
                            $el.removeAttr('checked');
                        }
                    }
                } else {
                    if (!$el.is('[readonly]')) {
                        if (defaultValue) {
                            $el.attr("value", defaultValue);
                        } else {
                            $el.attr("value", "");
                        }
                    }

                    if ($el.is('[readonly]') && $el.parent().find('.italsoft-button-ricerca').length) {
                        $el.attr("value", "");
                    }
                }

                if (this.name) {
                    $el.attr("name", "raccolta" + "[" + incId + "]" + $el.attr("name").substring(12));
                }

                if (this.id) {
                    $el.attr("id", $el.attr("id").slice(0, -2) + incId);
                }
            });

            $nuovaRaccolta.find('.ita-button-ricerca_dialog').each(function () {
                $(this).attr("id", $(this).attr("id").slice(0, -2) + incId);
            });

            $nuovaRaccolta.find('.ita-datepicker').each(function () {
                $(this).datepicker({
                    changeYear: true,
                    changeMonth: true,
                    dateFormat: 'dd/mm/yy',
                    showOn: "button",
                    showAnim: 'slideDown',
                    onSelect: function () {
                        $(this).focus();
                    },
                    yearRange: "-100:+10"

                })
                    .next(".ui-datepicker-trigger")
                    .html('<i class="icon ion-android-calendar italsoft-icon"></i>');

                $('input.ita-datepicker').mask("99/99/9999");
            });

            $nuovaRaccolta.find('.ita-time').each(function () {
                var time = $(this).val();
                var splittedTime = time.split(':');
                if (typeof (splittedTime[0]) != 'undefined' && splittedTime[1] != 'undefined') {
                    $(this).val(splittedTime[0] + ":" + splittedTime[1]);
                }
                $('input.ita-time').mask("99:99");
            });

            $nuovaRaccolta.find('[data-raccolta="01"]').each(function () {
                this.dataset.raccolta = incId;
            });

            $nuovaRaccolta.insertAfter("div.ita-box-raccolta:last");

            itaFrontOffice.parse($nuovaRaccolta);
            return;
        });
    });

    $(".ita-del-box").each(function () {
        $(this).click(function () {
            $("#boxRaccolta_" + $(this).attr("id").substring(13)).remove();
            return;
        });
    });
});

