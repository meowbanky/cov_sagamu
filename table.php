<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script src="table/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="table/datatables.min.css"/>
 
<script type="text/javascript" src="table/pdfmake.min.js"></script>
<script type="text/javascript" src="table/vfs_fonts.js"></script>
<script type="text/javascript" src="table/datatables.min.js"></script>

</head>

<body>


<table id="table_id" class="display">
    <thead>
        <tr>
            <th>Column 1</th>
            <th>Column 2</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Row 1 Data 1</td>
            <td>Row 1 Data 2</td>
        </tr>
        <tr>
            <td>Row 2 Data 1</td>
            <td>Row 2 Data 2</td>
        </tr>
    </tbody>
</table>

<script language="javascript">
                               $(document).ready( function () {$('#table_id').DataTable();} );
                               </script>
</body>
</html>