<?php
/**
 * Export Attendance to Excel API
 * Exports event attendance list to Excel format
 */

session_start();
require_once('../../Connections/cov.php');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Check authentication
if (!isset($_SESSION['UserID'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

mysqli_select_db($cov, $database_cov);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    $eventId = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
    
    if (!$eventId) {
        throw new Exception('Event ID is required', 400);
    }
    
    // Get event details
    $eventQuery = "SELECT * FROM events WHERE id = ?";
    $eventStmt = mysqli_prepare($cov, $eventQuery);
    mysqli_stmt_bind_param($eventStmt, "i", $eventId);
    mysqli_stmt_execute($eventStmt);
    $eventResult = mysqli_stmt_get_result($eventStmt);
    $event = mysqli_fetch_assoc($eventResult);
    mysqli_stmt_close($eventStmt);
    
    if (!$event) {
        throw new Exception('Event not found', 404);
    }
    
    // Get attendance list
    $attendanceQuery = "SELECT 
        ea.*,
        CONCAT(IFNULL(p.Lname, ''), ', ', IFNULL(p.Fname, ''), ' ', IFNULL(p.Mname, '')) as member_name
    FROM event_attendance ea
    LEFT JOIN tbl_personalinfo p ON p.memberid = ea.user_coop_id
    WHERE ea.event_id = ?
    ORDER BY ea.check_in_time DESC";
    
    $attStmt = mysqli_prepare($cov, $attendanceQuery);
    mysqli_stmt_bind_param($attStmt, "i", $eventId);
    mysqli_stmt_execute($attStmt);
    $attResult = mysqli_stmt_get_result($attStmt);
    
    $attendance = [];
    while ($row = mysqli_fetch_assoc($attResult)) {
        $attendance[] = $row;
    }
    mysqli_stmt_close($attStmt);
    
    // Try to use PhpSpreadsheet if available, otherwise fall back to CSV
    $usePhpSpreadsheet = file_exists(__DIR__ . '/../../vendor/autoload.php');
    
    if ($usePhpSpreadsheet) {
        require_once(__DIR__ . '/../../vendor/autoload.php');
        
        use PhpOffice\PhpSpreadsheet\Spreadsheet;
        use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
        use PhpOffice\PhpSpreadsheet\Style\Alignment;
        use PhpOffice\PhpSpreadsheet\Style\Border;
        use PhpOffice\PhpSpreadsheet\Style\Fill;
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Attendance');
        
        // Event information header
        $sheet->setCellValue('A1', 'Event: ' . $event['title']);
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A2', 'Date: ' . date('F j, Y', strtotime($event['start_time'])));
        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A3', 'Location: ' . $event['location_lat'] . ', ' . $event['location_lng']);
        $sheet->mergeCells('A3:F3');
        $sheet->setCellValue('A4', 'Total Attendees: ' . count($attendance));
        $sheet->mergeCells('A4:F4');
        
        // Headers
        $headers = ['S/N', 'Member Name', 'Member ID', 'Check-in Time', 'Distance (m)', 'Device ID', 'Status', 'Admin Override'];
        $col = 'A';
        $row = 6;
        
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }
        
        // Style header row
        $headerRange = 'A' . $row . ':' . chr(ord('A') + count($headers) - 1) . $row;
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        
        // Data rows
        $row++;
        $sn = 1;
        foreach ($attendance as $record) {
            $sheet->setCellValue('A' . $row, $sn);
            $sheet->setCellValue('B' . $row, trim($record['member_name'] ?? 'Unknown'));
            $sheet->setCellValue('C' . $row, $record['user_coop_id']);
            $sheet->setCellValue('D' . $row, date('Y-m-d H:i:s', strtotime($record['check_in_time'])));
            $sheet->setCellValue('E' . $row, number_format($record['distance_from_event'], 2));
            $sheet->setCellValue('F' . $row, $record['device_id'] ?? 'N/A');
            $sheet->setCellValue('G' . $row, ucfirst($record['status']));
            $sheet->setCellValue('H' . $row, ($record['admin_override'] ? 'Yes' : 'No'));
            
            // Alternate row colors
            if ($sn % 2 == 0) {
                $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F2F2F2']
                    ]
                ]);
            }
            
            $row++;
            $sn++;
        }
        
        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set filename
        $filename = 'Event_Attendance_' . preg_replace('/[^a-zA-Z0-9]/', '_', $event['title']) . '_' . date('Ymd');
        
        // Output
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
        
    } else {
        // Fall back to CSV
        $filename = 'Event_Attendance_' . preg_replace('/[^a-zA-Z0-9]/', '_', $event['title']) . '_' . date('Ymd') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Event info
        fputcsv($output, ['Event: ' . $event['title']]);
        fputcsv($output, ['Date: ' . date('F j, Y', strtotime($event['start_time']))]);
        fputcsv($output, ['Location: ' . $event['location_lat'] . ', ' . $event['location_lng']]);
        fputcsv($output, ['Total Attendees: ' . count($attendance)]);
        fputcsv($output, []); // Empty line
        
        // Headers
        fputcsv($output, ['S/N', 'Member Name', 'Member ID', 'Check-in Time', 'Distance (m)', 'Device ID', 'Status', 'Admin Override']);
        
        // Data
        $sn = 1;
        foreach ($attendance as $record) {
            fputcsv($output, [
                $sn,
                trim($record['member_name'] ?? 'Unknown'),
                $record['user_coop_id'],
                date('Y-m-d H:i:s', strtotime($record['check_in_time'])),
                number_format($record['distance_from_event'], 2),
                $record['device_id'] ?? 'N/A',
                ucfirst($record['status']),
                ($record['admin_override'] ? 'Yes' : 'No')
            ]);
            $sn++;
        }
        
        fclose($output);
        exit;
    }
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

