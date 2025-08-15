<?php
// Debug script to test the save function directly
require_once 'bank_statement_processor.php';

// Simulate the data that should be sent
$test_data = [
    'action' => 'save_extracted_data',
    'file_info' => [
        'filename' => 'test_statement.pdf',
        'file_path' => 'uploads/bank_statements/test_statement.pdf',
        'file_hash' => 'test_hash_' . time(),
        'period_id' => 1,
        'uploaded_by' => 1
    ],
    'transactions' => [
        [
            'name' => 'Test Transaction 1',
            'amount' => 100.00,
            'type' => 'credit',
            'description' => 'Test description 1',
            'date' => '2025-01-01',
            'matched' => true,
            'member_id' => 1,
            'member_name' => 'Test Member 1',
            'uniqueId' => 'test_1_' . time(),
            'page_info' => ['pageNumber' => 1]
        ],
        [
            'name' => 'Test Transaction 2',
            'amount' => 200.00,
            'type' => 'debit',
            'description' => 'Test description 2',
            'date' => '2025-01-02',
            'matched' => false,
            'member_id' => null,
            'member_name' => null,
            'uniqueId' => 'test_2_' . time(),
            'page_info' => ['pageNumber' => 1]
        ]
    ]
];

echo "Testing save function with data:\n";
echo json_encode($test_data, JSON_PRETTY_PRINT) . "\n\n";

// Call the save function
handleSaveExtractedData($test_data);
?>
