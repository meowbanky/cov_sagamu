function check(frmName) {
    if (document.getElementById('main').checked == 1) {
        selectAll(frmName);
    } else deselectAll(frmName);
}

function checkAll(cbId) {
    var master = '#' + cbId,
        ischecked = ($(master).attr('checked') == undefined) ? false : $(master).attr('checked');
    $(master).closest('.cbtogglegroup').find('input:checkbox').each(function() {
        if ($(this).attr('id') != cbId) {
            $(this).attr('checked', ischecked);
        }
    });
}

function checkMaster(cbId) {
    var thiscb = '#' + cbId;
    var master = '#' + $(thiscb).closest('div').find(':checkbox').attr('id');
    ischecked = ($(thiscb).attr('checked') == undefined) ? false : $(thiscb).attr('checked');
    if (master != cbId && ischecked == false) {
        $(master).attr('checked', false);
    }
}

function checkSenderLength(objTxtField) {
    if (isNaN(objTxtField.value)) {
        objTxtField.value = objTxtField.value.substring(0, 11);
    } else {
        objTxtField.value = objTxtField.value.substring(0, 14);
    }
}
var frm_contacts;
var frm_message;
var doublechars = new Array("[", "\\", "]", "^", "{", "|", "}", "~", "\u20ac");

function loadVars() {
    frm_contacts = document.forms['message'];
    frm_message = document.forms['message'];
}

function getMessageLength(message) {
    var msglen = 0;
    var strGSMTable = "";
    strGSMTable += "@Â£$Â¥Ã¨Ã©Ã¹Ã¬Ã²Ã‡Ã˜Ã¸Ã…Ã¥Î”_Î¦Î“Î›Î©Î Î¨Î£Î˜Îž`Ã†Ã¦ÃŸÃ‰ !\"#Â¤%&'()*+=,-./0123456789:;<=>?Â¡";
    strGSMTable += "ABCDEFGHIJKLMNOPQRSTUVWXYZÃ„Ã–Ã‘Ãœ`Â¿abcdefghijklmnopqrstuvwxyzÃ¤Ã¶Ã±Ã¼Ã ";
    strGSMTable += String.fromCharCode(10) + String.fromCharCode(13);
    var strExtendedTable = "^{}\\[~]|â‚¬";
    for (var i = 0; i < message.length; i++) {
        var cmessage = message.charAt(i);
        var intGSMTable = strGSMTable.indexOf(cmessage);
        if (intGSMTable != -1) {
            msglen += 1;
            continue;
        }
        var intExtendedTable = strExtendedTable.indexOf(cmessage);
        if (intExtendedTable != -1) {
            msglen += 2;
        } else {
            msglen += 8;
        }
    }
    return msglen;
}

function countPagesNCharacters() {
    var charRemDiv = null;
    var frm = document.message;
	var MAX_CHARS = 918;
    var obj_msg = document.getElementById('SentMessageMessage');
    if (obj_msg == null) {
        obj_msg = document.getElementById('ScheduledMessageMessage');
    }
    obj_msg.value = fixSmartQuotes(obj_msg.value);
    var message = obj_msg.value;
    var numChars = 0;
    if (document.getElementById) {
        charRemDiv = document.getElementById("charRem");
    } else {
        charRemDiv = document.all.charRem;
    }
    var msglen = getMessageLength(message);
    if (msglen > MAX_CHARS) {
        var excess = MAX_CHARS - (msglen - MAX_CHARS);
        message = message.substring(0, excess);
        if (document.getElementById('SentMessageMessage') == null) {
            document.getElementById('ScheduledMessageMessage').value = message;
        } else {
            document.getElementById('SentMessageMessage').value = message;
        }
    }
    var result = msglen % (MAX_CHARS + 1);
    if ((result == 0) && (msglen != 0)) {
        numChars = 0;
    } else {
        numChars = MAX_CHARS - result;
    }
    pages = (msglen > 160) ? 2 : 1;
    pages = (msglen > 306 && msglen <= 459) ? 3 : pages;
    pages = (msglen > 459 && msglen <= 612) ? 4 : pages;
    pages = (msglen > 612 && msglen <= 765) ? 5 : pages;
    pages = (msglen > 765 && msglen <= 918) ? 6 : pages;
    switch (pages) {
        case 2:
            numChars = 160 * 2 - result - 14;
            frm.chars.value = numChars + '/ ' + '306';
            frm.pages.value = pages;
            break;
        case 3:
            numChars = 160 * 3 - result - 14 - 7;
            frm.chars.value = numChars + '/ ' + '459';
            frm.pages.value = pages;
            break;
        case 4:
            numChars = 160 * 4 - result - 14 - 7 - 7;
            frm.chars.value = numChars + '/ ' + '612';
            frm.pages.value = pages;
            break;
        case 5:
            numChars = 160 * 5 - result - 14 - 7 - 7 - 7;
            frm.chars.value = numChars + '/ ' + '765';
            frm.pages.value = pages;
            break;
        case 6:
            numChars = 160 * 6 - result - 14 - 7 - 7 - 7 - 7;
            frm.chars.value = numChars + '/ ' + '918';
            frm.pages.value = pages;
            break;
        case 1:
        default:
            numChars = 160 - result;
            frm.chars.value = numChars + '/ ' + 160;
            frm.pages.value = pages;
            break;
    }
}

function countCharacters() {
    var charRemDiv = null;
    var frm = document.message;
    var PAGE_SIZE = 160;
    var MAX_CHARS = 120;
    var obj_msg = document.getElementById('SentMessageMessage');
    if (obj_msg == null) {
        obj_msg = document.getElementById('ScheduledMessageMessage');
    }
    obj_msg.value = fixSmartQuotes(obj_msg.value);
    var message = obj_msg.value;
    var numChars = 0;
    if (document.getElementById) {
        charRemDiv = document.getElementById("charRem");
    } else {
        charRemDiv = document.all.charRem;
    }
    var msglen = getMessageLength(message);
    if (msglen > MAX_CHARS) {
        var excess = MAX_CHARS - (msglen - MAX_CHARS);
        message = message.substring(0, excess);
        if (document.getElementById('SentMessageMessage') == null) {
            document.getElementById('ScheduledMessageMessage').value = message;
        } else {
            document.getElementById('SentMessageMessage').value = message;
        }
    }
    var result = msglen % (MAX_CHARS + 1);
    if ((result == 0) && (message.length != 0)) {
        numChars = 0;
    } else {
        numChars = MAX_CHARS - result;
    }
    pages = (message.length > PAGE_SIZE) ? 2 : 1;
    switch (pages) {
        case 2:
            numChars = MAX_CHARS * 2 - result - 14;
            frm.chars.value = numChars + '/ ' + MAX_CHARS * 2 - 14;
            frm.pages.value = pages;
            break;
        case 1:
        default:
            numChars = MAX_CHARS - result;
            frm.chars.value = numChars + '/ ' + MAX_CHARS;
            frm.pages.value = pages;
            break;
    }
}

function countRecipients() {
    var listcontacts = 0;
    for (i = 0; i < frm_contacts.elements.length; i++) {
        if (frm_contacts.elements[i].type == 'checkbox' && frm_contacts.elements[i].id != 'enable_schedule' && frm_contacts.elements[i].checked == true) {
            if (frm_contacts.elements[i].id == 0) {
                listcontacts = parseInt(frm_contacts.elements[i].value);
                break;
            } else {
                listcontacts += parseInt(frm_contacts.elements[i].value);
                if (isNaN(listcontacts)) listcontacts = 0;
            }
        }
    }
    frm_message.num_recipients.value = num_recipients + listcontacts;
}

function getContacts() {
    var theframe;
    var strfinal_numbers = '';
    var final_numbers = new Array();
    var sms_numbers = document.getElementById('SentMessageRecipient').value;
    var listcontacts = 0;
    theframe = window.frames[0].document.forms[0];
    if (window.frames[0].document.forms[0] != undefined) {
        inputCount = window.frames[0].document.forms[0].elements.length;
        for (i = 0; i < inputCount; i++) {
            if (theframe.elements[i].type == 'checkbox' && theframe.elements[i].value.length > 8)
                if (theframe.elements[i].checked == true)
                    final_numbers[final_numbers.length] = theframe.elements[i].value;
        }
    }
    strfinal_numbers = final_numbers.join(", ");
    sms_numbers = (sms_numbers.length > 8) ? sms_numbers + ', ' + strfinal_numbers : strfinal_numbers;
    sms_numbers = (strfinal_numbers.length > 8) ? sms_numbers + ', ' + strfinal_numbers : sms_numbers;
    document.getElementById('SentMessageRecipient').value = make_unique(sms_numbers);
    frm_message.num_recipients.value = num_recipients;
    for (i = 0; i < frm_contacts.elements.length; i++) {
        if (frm_contacts.elements[i].type == 'checkbox' && frm_contacts.elements[i].checked == true) {
            listcontacts += parseInt(frm_contacts.elements[i].value);
            if (isNaN(listcontacts)) listcontacts = 0;
            if (frm_contacts.elements[i].id == 0) break;
        }
    }
    frm_message.num_recipients.value = num_recipients + listcontacts;
}

function getEmails() {
    var theframe;
    var strfinal_numbers = '';
    var final_numbers = new Array();
    var sms_numbers = document.getElementById('SentMessageRecipient').value;
    theframe = window.frames[0].document.forms[0];
    if (window.frames[0].document.forms[0] != undefined) {
        inputCount = window.frames[0].document.forms[0].elements.length;
        for (i = 0; i < inputCount; i++) {
            if (theframe.elements[i].type == 'checkbox' && theframe.elements[i].value.length > 8)
                if (theframe.elements[i].checked == true)
                    final_numbers[final_numbers.length] = theframe.elements[i].value;
        }
    }
    strfinal_numbers = final_numbers.join(", ");
    sms_numbers = (sms_numbers.length > 8) ? sms_numbers + ', ' + strfinal_numbers : strfinal_numbers;
    sms_numbers = (strfinal_numbers.length > 8) ? sms_numbers + ', ' + strfinal_numbers : sms_numbers;
    document.getElementById('SentMessageRecipient').value = make_unique_emails(sms_numbers);
    frm_message.num_recipients.value = num_recipients;
    frm_message.num_recipients.value = num_recipients;
}

function make_unique_emails(numbers) {
    var regExp = /[a-z0-9\._-]+@+[a-z0-9\._-]+\.+[a-z]{2,3}/gi;
    var arr_numbers = numbers.match(regExp);
    if (arr_numbers == null) arr_numbers = Array(0);
    var arr_len = arr_numbers.length
    for (i = 0; i < arr_len; i++) {
        for (j = 0; j < arr_len; j++) {
            if (arr_numbers[i] == arr_numbers[j] && i != j) arr_numbers.splice(j, 1, 0);
        }
    }
    numbers = arr_numbers.join(', ');
    arr_numbers = numbers.match(regExp);
    if (arr_numbers == null) arr_numbers = Array(0);
    numbers = arr_numbers.join(', ');
    num_recipients = arr_numbers.length;
    return numbers;
}

function make_unique(numbers) {
    var regExp = /[0-9]{10,13}/g;
    var arr_numbers = numbers.match(regExp);
    if (arr_numbers == null) arr_numbers = Array(0);
    var arr_len = arr_numbers.length
    for (i = 0; i < arr_len; i++) {
        for (j = 0; j < arr_len; j++) {
            if (arr_numbers[i] == arr_numbers[j] && i != j) arr_numbers.splice(j, 1, 0);
        }
    }
    numbers = arr_numbers.join(',');
    arr_numbers = numbers.match(regExp);
    if (arr_numbers == null) arr_numbers = Array(0);
    numbers = arr_numbers.join(',');
    num_recipients = arr_numbers.length;
    return numbers;
}
var thisUrl = String(document.location);
var arr_params = thisUrl.split("/");
var masterId = arr_params[arr_params.length - 1];
var intsw_rate = 1.5;

function checkList() {
    if (parent.document.getElementById(masterId) == undefined) return false;
    var allchecked = true;
    var contactform;
    contactform = document.forms[0];
    inputCount = contactform.elements.length;
    for (i = 0; i < inputCount; i++) {
        if (contactform.elements[i].checked == false) allchecked = false;
    }
    if (allchecked == true) parent.document.getElementById(masterId).checked = 1;
    else parent.document.getElementById(masterId).checked = 0;
    return true;
}

function showEditableField(elem) {
    if (elem.selectedIndex == 0)
        document.getElementById('ContactListTitle').style.visibility = 'visible';
    else
        document.getElementById('ContactListTitle').style.visibility = 'hidden';
}
var prices = Array();

function calculateUnits(field, smsrate) {
    var smsrate = prices[0][2];
    var str = field.value;
    str = str.replace(',', '', 'g');
    str = str.replace(' ', '', 'g');
    var amountpaid = parseFloat(str);
    if (isNaN(amountpaid)) amountpaid = 0;
    if (isNaN(amountpaid)) amountpaid = 0;
    var i = prices.length - 1;
    for (count = i; count >= 0; count--) {
        if (amountpaid >= prices[count][0] * prices[count][2] && amountpaid <= prices[count][1] * prices[count][2]) {
            smsrate = prices[count][2];
            break;
        }
    }
    units = parseInt(amountpaid / smsrate);
    if (isNaN(units)) units = 0;
    else if (!isFinite(units)) units = 0;
    document.getElementById('UserPaymentQuantity').value = units;
}

function calculateCost1(field, smsrate) {
    var cost = Math.floor(field.value * smsrate)
    document.getElementById('UserPaymentAmountpaid').value = cost;
}

function checkPaymentMethod(url) {
    if (url == '') url = '/UserPayments/bankdeposit/';
    if (document.getElementById('UserPaymentUserPaymentMethodId1').checked == 1) {
        document.getElementById('onlinepaymentinfo').style.display = 'none';
        openDialogMax(this, url, 'Bank Deposit Form');
    } else {
        document.getElementById('onlinepaymentinfo').style.display = 'block';
    }
}

function checkPayment(field_id) {
    var unitsTobuy = 0;
    if (document.getElementById(field_id)) {
        unitsTobuy = parseFloat(document.getElementById(field_id).value);
    }
    if ((unitsTobuy >= prices[0][0]) && (unitsTobuy <= prices[prices.length - 1][1])) {
        return true;
    } else {
        var errMsg = 'You may purchase a minimum of ' + prices[0][0] + ' units, and a maximum of ' + prices[prices.length - 1][1] + ' online';
        return showPaymentProblemNotice(errMsg);
    }
}

function calculateCost(field, smsrate) {
    var errMsg = '',
        maxdailyspend = 150000;
    if (document.getElementById(field)) {
        field = document.getElementById(field);
    }
    var units = parseFloat(field.value);
    if (isNaN(units)) units = 0;
    var cost = units * smsrate;
    if (units < prices[0][0]) {
        cost = 0;
        document.getElementById('paymessage').innerHTML = 'You may purchase a minimum of ' + prices[0][0] + ' units';
    } else if (units >= prices[0][0] && units < prices[0][1]) {
        cost = parseFloat(field.value) * prices[0][2];
        document.getElementById('paymessage').innerHTML = "Unit Price: N" + prices[0][2].toFixed(2);
    } else if (units >= prices[1][0] && units <= prices[1][1]) {
        cost = units * prices[1][2];
        document.getElementById('paymessage').innerHTML = "Unit Price: N" + prices[1][2].toFixed(2);
    } else if (units >= prices[2][0] && units <= prices[2][1]) {
        cost = units * prices[2][2];
        document.getElementById('paymessage').innerHTML = "Unit Price: N" + prices[2][2].toFixed(2);
    } else if (units >= prices[3][0] && units <= prices[3][1]) {
        cost = units * prices[3][2];
        document.getElementById('paymessage').innerHTML = "Unit Price: N" + prices[3][2].toFixed(2);
    } else {
        cost = units * prices[3][2];
        document.getElementById('paymessage').innerHTML = "<u>U</u>nit Price: N" + prices[3][2].toFixed(2);
    }
    if (isNaN(cost)) cost = 0;
    field.value = units;
    document.getElementById('UserPaymentPrice').value = cost.toFixed(2);
    calculateIScharge('UserPaymentPrice');
    finalprice = parseFloat(cost) + parseFloat(document.getElementById('UserPaymentIntswCharge').value);
    document.getElementById('UserPaymentTotalprice').value = addCommas(finalprice.toFixed(2));
    if ((units < prices[0][0]) || (units > prices[prices.length - 1][1])) {
        errMsg = 'You may purchase a minimum of ' + prices[0][0] + ' units, and a maximum of ' + prices[prices.length - 1][1] + ' online';
        showPaymentProblemNotice(errMsg);
    } else if (finalprice > maxdailyspend) {
        errMsg = 'Sorry, you can only make transactions of a maximum of ' + maxdailyspend + ' naira daily. <br />If you wish to make payments above N' + maxdailyspend + ', please make a bank deposit<strong>.';
        showPaymentProblemNotice(errMsg);
    } else {
        hidePaymentProblemNotice();
    }
}

function calculateIScharge(price_id) {
    var is_cost = Math.ceil(document.getElementById(price_id).value * intsw_rate);
    is_cost = is_cost / 100;
    if (is_cost > 2500) {}
    document.getElementById('UserPaymentIntswCharge').value = is_cost.toFixed(2);
}

function toggleSchedule(objCheckbox) {
    if (objCheckbox.checked == true) {
        document.getElementById('btn_schedule').disabled = false;
        document.getElementById('ScheduledMessageDispatchtimeDay').disabled = false;
        document.getElementById('ScheduledMessageDispatchtimeMonth').disabled = false;
        document.getElementById('ScheduledMessageDispatchtimeYear').disabled = false;
        document.getElementById('ScheduledMessageDispatchtimeHour').disabled = false;
        document.getElementById('ScheduledMessageDispatchtimeMin').disabled = false;
        document.getElementById('ScheduledMessageDispatchtimeMeridian').disabled = false;
        document.getElementById('sendnow2').disabled = true;
        document.getElementById('savedraft2').disabled = true;
        document.getElementById('clear2').disabled = true;
    } else {
        document.getElementById('btn_schedule').disabled = true;
        document.getElementById('ScheduledMessageDispatchtimeDay').disabled = true;
        document.getElementById('ScheduledMessageDispatchtimeMonth').disabled = true;
        document.getElementById('ScheduledMessageDispatchtimeYear').disabled = true;
        document.getElementById('ScheduledMessageDispatchtimeHour').disabled = true;
        document.getElementById('ScheduledMessageDispatchtimeMin').disabled = true;
        document.getElementById('ScheduledMessageDispatchtimeMeridian').disabled = true;
        document.getElementById('sendnow2').disabled = false;
        document.getElementById('savedraft2').disabled = false;
        document.getElementById('clear2').disabled = false;
    }
}

function addCommas(sValue) {
    var sRegExp = new RegExp('(-?[0-9]+)([0-9]{3})');
    while (sRegExp.test(sValue)) {
        sValue = sValue.replace(sRegExp, '$1,$2');
    }
    return sValue;
}

function showPaymentProblemNotice(strMessage) {
    document.getElementById('paymentProblemNotice').style.display = 'block';
    document.getElementById('paymentProblemNotice').innerHTML = strMessage;
    document.getElementById('btnBuy').disabled = true;
    return false;
}

function hidePaymentProblemNotice() {
    document.getElementById('paymentProblemNotice').style.display = 'none';
    document.getElementById('paymentProblemNotice').innerHTML = '';
    document.getElementById('btnBuy').disabled = false;
    return true;
}

function fixSmartQuotes(str) {
    var replacements, regex, key;
    replacements = {
        "\xa0": " ",
        "\xa9": "(c)",
        "\xae": "(r)",
        "\xb7": "*",
        "\u2018": "'",
        "\u2019": "'",
        "\u201c": '"',
        "\u201d": '"',
        "\u2026": "...",
        "\u2002": " ",
        "\u2003": " ",
        "\u2009": " ",
        "\u2013": "-",
        "\u2014": "--",
        "\u2122": "(tm)"
    };
    regex = {};
    for (key in replacements) {
        regex[key] = new RegExp(key, 'g');
    }
    for (key in replacements) {
        str = str.replace(regex[key], replacements[key]);
    }
    return str;
}

function checkFile() {
    var fname = document.getElementById('PersonalisedMessageUserfilename').value;
    var ext = fname.substring(fname.length - 4);
    if (fname.length < 5) {
        alert('Please select a valid txt, csv, or xls file first!');
        return false;
    } else if (ext != '.xls' && ext != '.csv' && ext != '.txt') {
        alert('Please select a valid txt, csv, or xls file!' + '\n' + 'Selected file type (' + ext + ')');
        return false;
    } else {
        return true;
    }
}

function setShortText(elemId) {
    var shorttext = '';
    shorttext = shortenText(document.getElementById(elemId).value);
    document.getElementById(elemId).value = shorttext;
    document.getElementById(elemId).focus();
    document.getElementById(elemId).blur();
    document.getElementById(elemId).focus();
}

function shortenText(str) {
    var replacements, regex, key;
    replacements = {
        " at": " @",
        "be": "b",
        "ex": "x",
        "to": "2",
        "and": "&",
        "are": "r",
        "for": "4",
        "ing": "in",
        "see": "c",
        "you": "u",
        "You": "U",
        "your": "ur",
        "Your": "Ur",
        "love": "luv",
        "how": "hw",
        "the": "d",
        "The": "D",
        "night": "9te",
        "thank": "tnk",
        "thanks": "tnx",
        "this": "dis",
        "that": "dat",
        "they": "dey",
        "with": "wit",
        "today": "2day",
        "because": "bcos",
        "could": "cud",
        "tomorrow": "2moro",
        "know": "no",
        "true": "tru",
        "when": "wen",
        "good": "gud",
        "opposite": "opp"
    };
    regex = {};
    for (key in replacements) {
        regex[key] = new RegExp(key, 'g');
    }
    for (key in replacements) {
        str = str.replace(regex[key], replacements[key]);
    }
    return str;
}

function lengthInUtf8Bytes(str) {
    var m = encodeURIComponent(str).match(/%[89ABab]/g);
    return str.length + (m ? m.length : 0);
}

