(function ($) {
    "use strict";

    /*==================================================================
    [ Focus Contact2 ]*/
    $(".input3").each(function () {
        $(this).on("blur", function () {
            if ($(this).val().trim() != "") {
                $(this).addClass("has-val");
            } else {
                $(this).removeClass("has-val");
            }
        });
    });

    /*==================================================================
    [ Chose Radio ]*/
    $("#radio1").on("change", function () {
        if ($(this).is(":checked")) {
            $(".input3-select").slideUp(300);
        }
    });

    $("#radio2").on("change", function () {
        if ($(this).is(":checked")) {
            $(".input3-select").slideDown(300);
        }
    });

    /*==================================================================
    [ Validate ]*/
    //var name = $('.validate-input input[name="name"]');
    var firstname = $('.validate-input input[name="firstname"]');

    var email = $('.validate-input input[name="email"]');

    var mobile = $('.validate-input input[name="mobile"]');

    var account_no = $('.validate-input input[name="account_no"]');

    var bank = $('.validate-input input[name="bank"]');

    var surname = $('.validate-input input[name="surname"]');

    var firstname = $('.validate-input input[name="firstname"]');

    $(document).ready(function () {
        var l = $(".ladda-button").ladda();
        $(".validate-form").on("submit", function (event) {
            event.preventDefault();

            if ($(surname).val().trim() == "") {
                showValidate(surname);
                return false;
            }

            if ($(firstname).val().trim() == "") {
                showValidate(firstname);
                return false;
            }

            if (
                $(mobile).val().trim().length < 11 ||
                $(mobile).val().trim().length > 11
            ) {
                showValidate(mobile);
                return false;
            }

            var account_length = $(account_no).val().trim().length;

            if (account_length > 0) {
                if (account_length < 10 || account_length > 10) {
                    showValidate(account_no);
                    return false;
                }
            }

            //var selected = $("#bank option").filter(":selected").text();
            var selected = $("#bank");

            if (account_length == 10) {
                if (selected.val() === "") {
                    showValidate(bank);
                    return false;
                }
            }

            if (
                $(email)
                    .val()
                    .trim()
                    .match(
                        /^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{1,5}|[0-9]{1,3})(\]?)$/
                    ) == null
            ) {
                showValidate(email);
                return false;
            }

            var formValues = $(this).serialize();

            l.ladda("start");

            $.post("update.php", formValues)
                .done(function () {
                    $("#alert").html(
                        "<div class='alert alert-success alert-dismissible fade show' role='alert'> <strong>Information Saved !</strong><button type='button' class='close' data-bs-dismiss ='alert' aria-label='Close'>    <span aria-hidden='true'>&times;</span></button> </div>"
                    );

                    $(document).scrollTo(".alert", 2000);
                })
                .always(function () {
                    l.ladda("stop");
                });
            return false;
        });
    });

    function showValidate(input) {
        var thisAlert = $(input).parent();

        $(thisAlert).addClass("alert-validate");
    }

    function hideValidate(input) {
        var thisAlert = $(input).parent();

        $(thisAlert).removeClass("alert-validate");
    }
})(jQuery);
