<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test jQuery</title>
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<!-- Your form elements -->
<select id="fromPeriodI">
    <option value="1">Option 1</option>
    <option value="2">Option 2</option>
    <option value="3">Option 3</option>
</select>

<select id="toPeriodId">
    <option value="1">Option 1</option>
    <option value="2">Option 2</option>
    <option value="3">Option 3</option>
</select>

<!-- Your jQuery script -->
<script type="text/javascript">
    $(document).ready(function() {
        $("#fromPeriodI").change(function () {
            alert('ok');
            var selectedValue = $(this).val(); // Get the selected value from the fromPeriodI select element

            // Set the corresponding value in the toPeriodId select element
            $("#toPeriodId").val(selectedValue);
        });
    });
</script>

</body>
</html>
