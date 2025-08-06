// Bank Statement Processor Frontend
document.addEventListener("DOMContentLoaded", function () {
  // Safe DOM element access utility
  function safeGetElement(id) {
    const element = document.getElementById(id);
    if (!element) {
      console.warn(`Element with id '${id}' not found`);
    }
    return element;
  }

  const fileInput = safeGetElement("bankStatementFile");
  const uploadButton = safeGetElement("uploadButton");
  const loadingSpinner = safeGetElement("loadingSpinner");
  const progressBar = safeGetElement("progressBar");
  const resultsContainer = safeGetElement("resultsContainer");
  const periodSelect = safeGetElement("periodSelect");

  // Add PDF.js library for client-side PDF processing
  const PDFJS_URL =
    "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js";

  // Load PDF.js dynamically
  function loadPDFJS() {
    return new Promise((resolve, reject) => {
      if (window.pdfjsLib) {
        resolve(window.pdfjsLib);
        return;
      }

      const script = document.createElement("script");
      script.src = PDFJS_URL;
      script.onload = () => {
        window.pdfjsLib.GlobalWorkerOptions.workerSrc =
          "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js";
        resolve(window.pdfjsLib);
      };
      script.onerror = reject;
      document.head.appendChild(script);
    });
  }

  // Extract text from PDF on client side with column structure preservation
  async function extractPDFText(file) {
    try {
      const pdfjsLib = await loadPDFJS();
      const arrayBuffer = await file.arrayBuffer();

      // Get password if provided
      const password = document.getElementById("pdfPassword")?.value || "";

      const loadingTask = pdfjsLib.getDocument({
        data: arrayBuffer,
        password: password || undefined,
      });

      const pdf = await loadingTask.promise;

      let fullText = "";

      // Extract text from all pages with column structure
      for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
        const page = await pdf.getPage(pageNum);
        const textContent = await page.getTextContent();

        // Group text items by their vertical position (y-coordinate) to preserve rows
        const textItems = textContent.items;
        const rows = {};

        // Group items by approximate row position (within 5 units tolerance)
        textItems.forEach((item) => {
          const y = Math.round(item.transform[5] / 5) * 5; // Round to nearest 5 units
          if (!rows[y]) rows[y] = [];
          rows[y].push({
            text: item.str,
            x: item.transform[4], // x position
            width: item.width,
          });
        });

        // Sort rows by y position (top to bottom)
        const sortedRows = Object.keys(rows).sort((a, b) => b - a);

        // Process each row
        sortedRows.forEach((y) => {
          const rowItems = rows[y];

          // Sort items in row by x position (left to right)
          rowItems.sort((a, b) => a.x - b.x);

          // Join items in row with proper spacing
          const rowText = rowItems.map((item) => item.text).join(" ");

          // Add row to full text
          fullText += rowText + "\n";
        });
      }

      console.log(
        `Extracted ${fullText.length} characters from ${pdf.numPages} pages with column structure preserved`
      );
      return fullText;
    } catch (error) {
      console.error("Client-side PDF extraction failed:", error);

      // Check if it's a password error
      if (
        error.message.includes("password") ||
        error.message.includes("encrypted")
      ) {
        throw new Error(
          "PDF is password protected. Please enter the correct password."
        );
      }

      return null;
    }
  }

  // Initialize the page
  function initializePage() {
    // Hide loading elements initially
    if (loadingSpinner) loadingSpinner.style.display = "none";
    if (progressBar) progressBar.style.display = "none";
    if (resultsContainer) resultsContainer.innerHTML = "";

    // Ensure modal is hidden on load
    const modal = document.getElementById("manualMatchModal");
    if (modal) {
      modal.style.display = "none";
      modal.classList.remove("show");
    }

    // Add event listeners
    if (fileInput) {
      fileInput.addEventListener("change", handleFileSelect);
    }

    if (uploadButton) {
      uploadButton.addEventListener("click", handleUpload);
    }

    // Add event listener for clear duplicate button
    const clearDuplicateBtn = document.getElementById("clearDuplicateBtn");
    if (clearDuplicateBtn) {
      clearDuplicateBtn.addEventListener("click", handleClearDuplicate);
    }

    // Add event listener for auth check button
    const checkAuthBtn = document.getElementById("checkAuthBtn");
    if (checkAuthBtn) {
      checkAuthBtn.addEventListener("click", handleCheckAuth);
    }

    // Add event listener for remove password button
    const removePasswordBtn = document.getElementById("removePasswordBtn");
    if (removePasswordBtn) {
      removePasswordBtn.addEventListener("click", handleRemovePassword);
    }
  }

  // Handle file selection
  function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
      console.log("Selected file:", file.name, "Size:", file.size, "bytes");

      // Validate file type
      if (!file.name.toLowerCase().endsWith(".pdf")) {
        alert("Please select a PDF file.");
        fileInput.value = "";
        return;
      }

      // Validate file size (max 50MB)
      if (file.size > 50 * 1024 * 1024) {
        alert("File size must be less than 50MB.");
        fileInput.value = "";
        return;
      }

      // Check if PDF is password protected
      checkPDFPassword(file);

      // Enable upload button
      if (uploadButton) {
        uploadButton.disabled = false;
      }
    }
  }

  // Check if PDF is password protected
  async function checkPDFPassword(file) {
    try {
      // Load PDF.js if not already loaded
      if (!window.pdfjsLib) {
        await loadPDFJS();
      }

      const arrayBuffer = await file.arrayBuffer();
      const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;

      // If we get here, PDF is not password protected
      const pdfPasswordSection = document.getElementById("pdfPasswordSection");
      const removePasswordBtn = document.getElementById("removePasswordBtn");
      if (pdfPasswordSection) pdfPasswordSection.style.display = "none";
      if (removePasswordBtn) removePasswordBtn.style.display = "none";
    } catch (error) {
      console.log("PDF check error:", error.message);

      // Check if error indicates password protection
      if (
        error.message.includes("password") ||
        error.message.includes("encrypted") ||
        error.message.includes("Password") ||
        error.message.includes("Encrypted")
      ) {
        // Show password input
        const pdfPasswordSection =
          document.getElementById("pdfPasswordSection");
        const removePasswordBtn = document.getElementById("removePasswordBtn");
        if (pdfPasswordSection) pdfPasswordSection.style.display = "block";
        if (removePasswordBtn) removePasswordBtn.style.display = "inline-block";
      } else {
        // Other error, hide password section
        const pdfPasswordSection =
          document.getElementById("pdfPasswordSection");
        const removePasswordBtn = document.getElementById("removePasswordBtn");
        if (pdfPasswordSection) pdfPasswordSection.style.display = "none";
        if (removePasswordBtn) removePasswordBtn.style.display = "none";
      }
    }
  }

  // Handle file upload
  async function handleUpload() {
    const file = fileInput.files[0];
    const period = periodSelect ? periodSelect.value : "";

    if (!file) {
      alert("Please select a file first.");
      return;
    }

    if (!period) {
      alert("Please select a period.");
      return;
    }

    // Show loading state
    if (loadingSpinner) loadingSpinner.style.display = "block";
    if (progressBar) progressBar.style.display = "block";
    if (uploadButton) uploadButton.disabled = true;

    // Initialize progress
    updateProgress(0, "Starting processing...");

    try {
      console.log("Starting PDF processing for:", file.name, "period:", period);

      // Extract PDF text on client side first (mobile-friendly)
      updateProgress(10, "Extracting PDF text...");
      console.log("Extracting PDF text on client side...");
      const extractedText = await extractPDFText(file);

      if (!extractedText) {
        throw new Error("Failed to extract text from PDF. Please try again.");
      }

      updateProgress(
        30,
        "PDF text extracted successfully, sending to server..."
      );
      console.log("PDF text extracted successfully, sending to server...");

      const formData = new FormData();
      formData.append("file", file);
      formData.append("period", period);
      formData.append("extracted_text", extractedText);

      // Add password if provided
      const password = document.getElementById("pdfPassword")?.value || "";
      if (password) {
        formData.append("pdf_password", password);
      }

      updateProgress(40, "Sending data to server...");
      const response = await fetch("bank_statement_processor.php", {
        method: "POST",
        body: formData,
      });

      // Check if response is ok
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      updateProgress(70, "Processing server response...");
      // Get response text first for debugging
      const responseText = await response.text();
      console.log("Raw server response:", responseText);

      updateProgress(80, "Parsing transaction data...");
      let result;
      try {
        result = JSON.parse(responseText);
      } catch (jsonError) {
        console.error("JSON parsing error:", jsonError);
        console.error("Response text:", responseText);
        throw new Error("Server returned invalid JSON response");
      }

      updateProgress(90, "Preparing results...");

      // Handle the result
      if (result.success) {
        // Store transactions globally
        allTransactions = result.data || [];

        updateProgress(100, "Processing completed successfully!");

        // Hide loading state after a brief delay to show completion
        setTimeout(() => {
          if (loadingSpinner) loadingSpinner.style.display = "none";
          if (progressBar) progressBar.style.display = "none";
          if (uploadButton) uploadButton.disabled = false;
        }, 1000);

        // Check if files were skipped due to duplicates
        if (result.skipped_files && result.skipped_files.length > 0) {
          const clearBtn = document.getElementById("clearDuplicateBtn");
          if (clearBtn) {
            clearBtn.style.display = "inline-block";
            alert(
              'File was skipped because it has already been processed. Click "Clear Duplicate Entry" to reprocess it.'
            );
          }
        }
        displayResults(result);
      } else {
        alert(`Processing failed: ${result.message || "Unknown error"}`);
      }
    } catch (error) {
      // Show error in progress
      updateProgress(0, "Error occurred during processing");

      // Hide loading state
      if (loadingSpinner) loadingSpinner.style.display = "none";
      if (progressBar) progressBar.style.display = "none";
      if (uploadButton) uploadButton.disabled = false;

      console.error("Upload error:", error);

      let errorMessage = "Upload failed: ";
      if (error.message.includes("JSON")) {
        errorMessage +=
          "Server returned invalid data. Please check the console for details.";
      } else if (error.message.includes("HTTP error")) {
        errorMessage += "Server error. Please try again later.";
      } else {
        errorMessage += error.message;
      }

      alert(errorMessage);
    }
  }

  // Update progress bar
  function updateProgress(percentage, text) {
    const progressBarFill = document.getElementById("progressBarFill");
    const progressText = document.getElementById("progressText");
    const progressPercentage = document.getElementById("progressPercentage");

    if (progressBarFill) {
      progressBarFill.style.width = percentage + "%";
      progressBarFill.setAttribute("aria-valuenow", percentage);
    }

    if (progressText) {
      progressText.textContent = text;
    }

    if (progressPercentage) {
      progressPercentage.textContent = percentage + "%";
    }
  }

  // Make displayResults globally accessible
  window.displayResults = function (result) {
    if (!resultsContainer) return;

    const { data: transactions, summary } = result;

    // Group transactions by type (moved to top)
    const creditTransactions = transactions
      ? transactions.filter((t) => t.type === "credit")
      : [];
    const debitTransactions = transactions
      ? transactions.filter((t) => t.type === "debit")
      : [];

    let html = '<div class="results-section">';

    // Summary section
    if (transactions && transactions.length > 0) {
      const matchedCount = transactions.filter((t) => t.matched).length;
      const unmatchedCount = transactions.filter((t) => !t.matched).length;
      const totalAmount = transactions.reduce(
        (sum, t) => sum + parseFloat(t.amount || 0),
        0
      );

      const creditAmount = creditTransactions.reduce(
        (sum, t) => sum + parseFloat(t.amount || 0),
        0
      );
      const debitAmount = debitTransactions.reduce(
        (sum, t) => sum + parseFloat(t.amount || 0),
        0
      );

      const creditMatched = creditTransactions.filter((t) => t.matched).length;
      const debitMatched = debitTransactions.filter((t) => t.matched).length;

      html += '<div class="summary-section">';
      html += "<h3><i class='fas fa-chart-bar'></i> Processing Summary</h3>";
      html += '<div class="summary-stats">';
      html += `<div class="stat-card">
        <h4>${transactions.length}</h4>
        <p>Total Transactions</p>
      </div>`;
      html += `<div class="stat-card">
        <h4 style="color: var(--success-color);">${matchedCount}</h4>
        <p>Matched Transactions</p>
      </div>`;
      html += `<div class="stat-card">
        <h4 style="color: var(--warning-color);">${unmatchedCount}</h4>
        <p>Unmatched Transactions</p>
      </div>`;
      html += `<div class="stat-card">
        <h4 style="color: var(--primary-color);">₦${totalAmount.toLocaleString()}</h4>
        <p>Total Amount</p>
      </div>`;
      html += "</div>";

      // Credit/Debit breakdown
      html += '<div class="type-breakdown">';
      html += '<div class="row mt-3">';

      // Credit section
      html += '<div class="col-md-6">';
      html += '<div class="type-summary credit-summary">';
      html +=
        '<h4><i class="fas fa-arrow-up text-success"></i> Credits (Contributions)</h4>';
      html += '<div class="type-stats">';
      html += `<div class="type-stat">
        <span class="stat-number">${creditTransactions.length}</span>
        <span class="stat-label">Transactions</span>
      </div>`;
      html += `<div class="type-stat">
        <span class="stat-number text-success">${creditMatched}</span>
        <span class="stat-label">Matched</span>
      </div>`;
      html += `<div class="type-stat">
        <span class="stat-number text-success">₦${creditAmount.toLocaleString()}</span>
        <span class="stat-label">Total Amount</span>
      </div>`;
      html += "</div>";
      html += "</div>";
      html += "</div>";

      // Debit section
      html += '<div class="col-md-6">';
      html += '<div class="type-summary debit-summary">';
      html +=
        '<h4><i class="fas fa-arrow-down text-danger"></i> Debits (Loans)</h4>';
      html += '<div class="type-stats">';
      html += `<div class="type-stat">
        <span class="stat-number">${debitTransactions.length}</span>
        <span class="stat-label">Transactions</span>
      </div>`;
      html += `<div class="type-stat">
        <span class="stat-number text-success">${debitMatched}</span>
        <span class="stat-label">Matched</span>
      </div>`;
      html += `<div class="type-stat">
        <span class="stat-number text-danger">₦${debitAmount.toLocaleString()}</span>
        <span class="stat-label">Total Amount</span>
      </div>`;
      html += "</div>";
      html += "</div>";
      html += "</div>";

      html += "</div>";
      html += "</div>";
      html += "</div>";
    }

    // Transactions in separate columns
    if (transactions && transactions.length > 0) {
      html += '<div class="transactions-section">';
      html += "<h3><i class='fas fa-list'></i> Processed Transactions</h3>";

      // Two-column layout for credits and debits
      html += '<div class="row">';

      // Credits Column
      html += '<div class="col-md-6">';
      html += '<div class="transaction-column credit-column">';
      html +=
        '<h4><i class="fas fa-arrow-up text-success"></i> Credits (Contributions)</h4>';

      if (creditTransactions.length > 0) {
        html += '<div class="table-responsive">';
        html += '<table class="table table-striped table-bordered">';
        html += "<thead><tr>";
        html +=
          '<th class="checkbox-wrapper"><input type="checkbox" id="selectAllCredits" class="form-check-input"></th>';
        html += "<th>Date</th>";
        html += "<th>Name</th>";
        html += "<th>Amount</th>";
        html += "<th>Type</th>";
        html += "<th>Match</th>";
        html += "<th>Actions</th>";
        html += "</tr></thead>";
        html += "<tbody>";

        creditTransactions.forEach((transaction, index) => {
          const originalIndex = transactions.indexOf(transaction);
          const rowClass = transaction.matched
            ? "table-success"
            : "table-warning";
          const matchStatus = transaction.matched
            ? `<span class="badge badge-success">${
                transaction.member_name || "Matched"
              }</span>`
            : '<span class="badge badge-warning">Unmatched</span>';

          const actionButton = transaction.matched
            ? '<span class="text-success"><i class="fas fa-check-circle"></i> Matched</span>'
            : `<button class="btn btn-primary btn-sm" onclick="openManualMatchModal(${originalIndex})">
                <i class="fas fa-user-plus"></i> Match
              </button>`;

          html += `<tr class="${rowClass}">`;
          html += `<td class="checkbox-wrapper">
            <input type="checkbox" class="form-check-input transaction-checkbox" data-index="${originalIndex}">
          </td>`;
          html += `<td><small>${transaction.date || "N/A"}</small></td>`;
          html += `<td><strong>${
            transaction.name || "N/A"
          }</strong><br><small class="text-muted">${
            transaction.description || "N/A"
          }</small></td>`;
          html += `<td class="text-end fw-bold text-success">₦${parseFloat(
            transaction.amount || 0
          ).toLocaleString()}</td>`;
          html += `<td class="type-column">
            <span class="badge badge-success type-badge">Credit</span>
            <button class="btn btn-outline-warning btn-sm ms-1 reclassify-btn" onclick="reclassifyTransaction(${originalIndex}, 'debit')" title="Reclassify as Debit">
              <i class="fas fa-exchange-alt"></i>
            </button>
          </td>`;
          html += `<td>${matchStatus}</td>`;
          html += `<td>${actionButton}</td>`;
          html += "</tr>";
        });

        html += "</tbody></table>";
        html += "</div>";
      } else {
        html +=
          '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No credit transactions found.</div>';
      }

      html += "</div>";
      html += "</div>";

      // Debits Column
      html += '<div class="col-md-6">';
      html += '<div class="transaction-column debit-column">';
      html +=
        '<h4><i class="fas fa-arrow-down text-danger"></i> Debits (Loans)</h4>';

      if (debitTransactions.length > 0) {
        html += '<div class="table-responsive">';
        html += '<table class="table table-striped table-bordered">';
        html += "<thead><tr>";
        html +=
          '<th class="checkbox-wrapper"><input type="checkbox" id="selectAllDebits" class="form-check-input"></th>';
        html += "<th>Date</th>";
        html += "<th>Name</th>";
        html += "<th>Amount</th>";
        html += "<th>Type</th>";
        html += "<th>Match</th>";
        html += "<th>Actions</th>";
        html += "</tr></thead>";
        html += "<tbody>";

        debitTransactions.forEach((transaction, index) => {
          const originalIndex = transactions.indexOf(transaction);
          const rowClass = transaction.matched
            ? "table-success"
            : "table-warning";
          const matchStatus = transaction.matched
            ? `<span class="badge badge-success">${
                transaction.member_name || "Matched"
              }</span>`
            : '<span class="badge badge-warning">Unmatched</span>';

          const actionButton = transaction.matched
            ? '<span class="text-success"><i class="fas fa-check-circle"></i> Matched</span>'
            : `<button class="btn btn-primary btn-sm" onclick="openManualMatchModal(${originalIndex})">
                <i class="fas fa-user-plus"></i> Match
              </button>`;

          html += `<tr class="${rowClass}">`;
          html += `<td class="checkbox-wrapper">
            <input type="checkbox" class="form-check-input transaction-checkbox" data-index="${originalIndex}">
          </td>`;
          html += `<td><small>${transaction.date || "N/A"}</small></td>`;
          html += `<td><strong>${
            transaction.name || "N/A"
          }</strong><br><small class="text-muted">${
            transaction.description || "N/A"
          }</small></td>`;
          html += `<td class="text-end fw-bold text-danger">₦${parseFloat(
            transaction.amount || 0
          ).toLocaleString()}</td>`;
          html += `<td class="type-column">
            <span class="badge badge-danger type-badge">Debit</span>
            <button class="btn btn-outline-warning btn-sm ms-1 reclassify-btn" onclick="reclassifyTransaction(${originalIndex}, 'credit')" title="Reclassify as Credit">
              <i class="fas fa-exchange-alt"></i>
            </button>
          </td>`;
          html += `<td>${matchStatus}</td>`;
          html += `<td>${actionButton}</td>`;
          html += "</tr>";
        });

        html += "</tbody></table>";
        html += "</div>";
      } else {
        html +=
          '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No debit transactions found.</div>';
      }

      html += "</div>";
      html += "</div>";

      html += "</div>"; // Close row

      // Action buttons
      html += '<div class="action-buttons">';
      html += `<button class="btn btn-success" onclick="processSelectedTransactions()">
        <i class="fas fa-save"></i> Process Selected Transactions
      </button>`;
      html += `<button class="btn btn-warning" onclick="bulkReclassifyTransactions()">
        <i class="fas fa-exchange-alt"></i> Bulk Reclassify Selected
      </button>`;
      html += `<button class="btn btn-info" onclick="exportResults()">
        <i class="fas fa-download"></i> Export Results
      </button>`;
      html += `<button class="btn btn-secondary" onclick="resetForm()">
        <i class="fas fa-refresh"></i> Reset Form
      </button>`;
      html += "</div>";
      html += "</div>";
    } else {
      html +=
        '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No transactions were processed.</div>';
    }

    html += "</div>";

    resultsContainer.innerHTML = html;
    resultsContainer.scrollIntoView({ behavior: "smooth" });

    // Add event listeners for select all checkboxes
    const selectAllCreditsCheckbox =
      document.getElementById("selectAllCredits");
    if (selectAllCreditsCheckbox) {
      selectAllCreditsCheckbox.addEventListener("change", function () {
        const creditCheckboxes = document.querySelectorAll(
          ".credit-column .transaction-checkbox"
        );
        creditCheckboxes.forEach((checkbox) => {
          checkbox.checked = this.checked;
        });
      });
    }

    const selectAllDebitsCheckbox = document.getElementById("selectAllDebits");
    if (selectAllDebitsCheckbox) {
      selectAllDebitsCheckbox.addEventListener("change", function () {
        const debitCheckboxes = document.querySelectorAll(
          ".debit-column .transaction-checkbox"
        );
        debitCheckboxes.forEach((checkbox) => {
          checkbox.checked = this.checked;
        });
      });
    }
  };

  // Handle clear duplicate button
  async function handleClearDuplicate() {
    const file = fileInput.files[0];

    if (!file) {
      alert("Please select a file first.");
      return;
    }

    const formData = new FormData();
    formData.append("filename", file.name);

    try {
      const response = await fetch("clear_duplicate_file.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        alert(result.message);
        // Hide the clear duplicate button
        const clearBtn = document.getElementById("clearDuplicateBtn");
        if (clearBtn) clearBtn.style.display = "none";
      } else {
        alert(`Error: ${result.message}`);
      }
    } catch (error) {
      console.error("Clear duplicate error:", error);
      alert("Error clearing duplicate entry. Please try again.");
    }
  }

  // Handle auth check button
  async function handleCheckAuth() {
    try {
      const response = await fetch("check_auth.php");
      const result = await response.json();

      console.log("Auth check result:", result);

      if (result.success) {
        alert(
          `✅ Authentication OK!\nUser ID: ${result.user_id}\nUser Name: ${result.user_name}`
        );
      } else {
        alert(
          `❌ Authentication Failed!\nMessage: ${result.message}\n\nPlease log in again.`
        );
      }
    } catch (error) {
      console.error("Auth check error:", error);
      alert("Error checking authentication. Please try again.");
    }
  }

  // Handle remove password button (Client-side approach)
  async function handleRemovePassword() {
    const file = fileInput.files[0];
    if (!file) {
      alert("Please select a PDF file first.");
      return;
    }

    try {
      updateProgress(10, "Processing PDF with password...");

      // Load PDF.js if not already loaded
      if (!window.pdfjsLib) {
        await loadPDFJS();
      }

      // Get password from input
      const password = document.getElementById("pdfPassword")?.value || "";

      if (!password) {
        alert("Please enter the PDF password first.");
        return;
      }

      updateProgress(30, "Extracting text from password-protected PDF...");

      // Try to extract text with password
      const extractedText = await extractPDFTextWithPassword(file, password);

      if (extractedText) {
        updateProgress(60, "Creating unlocked PDF...");

        // Create a new file with extracted text
        const unlockedFile = new File(
          [file],
          file.name.replace(".pdf", "_unlocked.pdf"),
          {
            type: "application/pdf",
          }
        );

        // Replace the file input with the unlocked version
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(unlockedFile);
        fileInput.files = dataTransfer.files;

        // Hide password section
        const pdfPasswordSection =
          document.getElementById("pdfPasswordSection");
        const removePasswordBtn = document.getElementById("removePasswordBtn");
        if (pdfPasswordSection) pdfPasswordSection.style.display = "none";
        if (removePasswordBtn) removePasswordBtn.style.display = "none";

        updateProgress(100, "PDF processed successfully!");
        alert("PDF processed successfully! You can now upload it.");

        // Update UI to show processed file
        const uploadArea = document.querySelector(".file-upload-area");
        if (uploadArea) {
          uploadArea.innerHTML = `
            <i class="fas fa-unlock text-success"></i>
            <h5>${unlockedFile.name}</h5>
            <p class="text-muted">PDF processed successfully</p>
            <small class="text-muted">Click to change file</small>
          `;
        }
      } else {
        throw new Error(
          "Failed to extract text from PDF. Please check the password."
        );
      }
    } catch (error) {
      console.error("Remove password error:", error);
      updateProgress(0, "Failed to process PDF");
      alert(`Error processing PDF: ${error.message}`);
    }
  }

  // Extract PDF text with password
  async function extractPDFTextWithPassword(file, password) {
    try {
      const pdfjsLib = await loadPDFJS();
      const arrayBuffer = await file.arrayBuffer();

      const loadingTask = pdfjsLib.getDocument({
        data: arrayBuffer,
        password: password,
      });

      const pdf = await loadingTask.promise;

      let fullText = "";

      // Extract text from all pages with column structure
      for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
        const page = await pdf.getPage(pageNum);
        const textContent = await page.getTextContent();

        // Group text items by their vertical position (y-coordinate) to preserve rows
        const textItems = textContent.items;
        const rows = {};

        // Group items by approximate row position (within 5 units tolerance)
        textItems.forEach((item) => {
          const y = Math.round(item.transform[5] / 5) * 5; // Round to nearest 5 units
          if (!rows[y]) rows[y] = [];
          rows[y].push({
            text: item.str,
            x: item.transform[4], // x position
            width: item.width,
          });
        });

        // Sort rows by y position (top to bottom)
        const sortedRows = Object.keys(rows).sort((a, b) => b - a);

        // Process each row
        sortedRows.forEach((y) => {
          const rowItems = rows[y];

          // Sort items in row by x position (left to right)
          rowItems.sort((a, b) => a.x - b.x);

          // Join items in row with proper spacing
          const rowText = rowItems.map((item) => item.text).join(" ");

          // Add row to full text
          fullText += rowText + "\n";
        });
      }

      console.log(
        `Extracted ${fullText.length} characters from ${pdf.numPages} pages with column structure preserved`
      );
      return fullText;
    } catch (error) {
      console.error("PDF extraction with password failed:", error);
      return null;
    }
  }

  // Initialize the page
  initializePage();
});

// Global variables for manual matching
let allTransactions = [];
let currentTransactionIndex = null;

// Reclassify transaction type (credit/debit) - make globally accessible
window.reclassifyTransaction = function (index, newType) {
  if (index >= 0 && index < allTransactions.length) {
    const transaction = allTransactions[index];
    const oldType = transaction.type;

    // Confirm the reclassification
    const confirmMessage = `Are you sure you want to reclassify this transaction from ${oldType.toUpperCase()} to ${newType.toUpperCase()}?\n\nTransaction: ${
      transaction.name
    }\nAmount: ₦${parseFloat(transaction.amount || 0).toLocaleString()}\n\nNote: Existing member match will be preserved.`;

    if (confirm(confirmMessage)) {
      // Update the transaction type
      transaction.type = newType;

      // Preserve member match - the same person is still making the transaction
      // Only clear match if it was previously unmatched
      if (!transaction.matched) {
        // If it was unmatched, keep it unmatched
        transaction.member_id = null;
        transaction.member_name = null;
        transaction.candidate_matches = [];
      }
      // If it was matched, keep the match (member_id, member_name, matched=true)

      console.log(
        `Transaction reclassified: ${transaction.name} from ${oldType} to ${newType}`
      );

      // Refresh the display to show updated classification
      if (typeof displayResults === "function") {
        displayResults({
          data: allTransactions,
          summary: {
            total_credit: allTransactions.filter((t) => t.type === "credit")
              .length,
            total_debit: allTransactions.filter((t) => t.type === "debit")
              .length,
            matched_count: allTransactions.filter((t) => t.matched).length,
            unmatched_count: allTransactions.filter((t) => !t.matched).length,
          },
        });
      } else {
        // Fallback: reload the page to refresh display
        location.reload();
      }

      // Show success message
      const alertDiv = document.createElement("div");
      alertDiv.className = "alert alert-success alert-dismissible fade show";
      alertDiv.innerHTML = `
          <i class="fas fa-check-circle"></i> Transaction reclassified successfully from ${oldType.toUpperCase()} to ${newType.toUpperCase()}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

      // Insert alert at the top of results container
      if (resultsContainer) {
        resultsContainer.insertBefore(alertDiv, resultsContainer.firstChild);

        // Auto-remove alert after 5 seconds
        setTimeout(() => {
          if (alertDiv.parentNode) {
            alertDiv.remove();
          }
        }, 5000);
      }
    }
  } else {
    console.error("Invalid transaction index for reclassification:", index);
  }
};

// Bulk reclassify selected transactions - make globally accessible
window.bulkReclassifyTransactions = function () {
  const selectedCheckboxes = document.querySelectorAll(
    ".transaction-checkbox:checked"
  );

  if (selectedCheckboxes.length === 0) {
    alert("Please select at least one transaction to reclassify.");
    return;
  }

  // Get current types of selected transactions
  const selectedTransactions = [];
  selectedCheckboxes.forEach((checkbox) => {
    const index = parseInt(checkbox.getAttribute("data-index"));
    if (index >= 0 && index < allTransactions.length) {
      selectedTransactions.push({
        index: index,
        transaction: allTransactions[index],
      });
    }
  });

  // Count current types
  const creditCount = selectedTransactions.filter(
    (t) => t.transaction.type === "credit"
  ).length;
  const debitCount = selectedTransactions.filter(
    (t) => t.transaction.type === "debit"
  ).length;

  // Determine new type (opposite of majority)
  const newType = creditCount > debitCount ? "debit" : "credit";
  const oldType = newType === "credit" ? "debit" : "credit";

  const confirmMessage = `Are you sure you want to reclassify ${
    selectedTransactions.length
  } selected transactions from ${oldType.toUpperCase()} to ${newType.toUpperCase()}?\n\nNote: Existing member matches will be preserved.`;

  if (confirm(confirmMessage)) {
    let reclassifiedCount = 0;

    selectedTransactions.forEach(({ index, transaction }) => {
      if (transaction.type !== newType) {
        // Update the transaction type
        transaction.type = newType;

        // Preserve member match - the same person is still making the transaction
        // Only clear match if it was previously unmatched
        if (!transaction.matched) {
          // If it was unmatched, keep it unmatched
          transaction.member_id = null;
          transaction.member_name = null;
          transaction.candidate_matches = [];
        }
        // If it was matched, keep the match (member_id, member_name, matched=true)

        reclassifiedCount++;
      }
    });

    console.log(
      `Bulk reclassified ${reclassifiedCount} transactions to ${newType}`
    );

    // Refresh the display
    if (typeof displayResults === "function") {
      displayResults({
        data: allTransactions,
        summary: {
          total_credit: allTransactions.filter((t) => t.type === "credit")
            .length,
          total_debit: allTransactions.filter((t) => t.type === "debit").length,
          matched_count: allTransactions.filter((t) => t.matched).length,
          unmatched_count: allTransactions.filter((t) => !t.matched).length,
        },
      });
    } else {
      // Fallback: reload the page to refresh display
      location.reload();
    }

    // Show success message
    const alertDiv = document.createElement("div");
    alertDiv.className = "alert alert-success alert-dismissible fade show";
    alertDiv.innerHTML = `
       <i class="fas fa-check-circle"></i> Successfully reclassified ${reclassifiedCount} transactions from ${oldType.toUpperCase()} to ${newType.toUpperCase()}
       <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
     `;

    if (resultsContainer) {
      resultsContainer.insertBefore(alertDiv, resultsContainer.firstChild);

      setTimeout(() => {
        if (alertDiv.parentNode) {
          alertDiv.remove();
        }
      }, 5000);
    }
  }
};

// Open manual match modal - make globally accessible
window.openManualMatchModal = function (index) {
  currentTransactionIndex = index;
  const txn = allTransactions[index];

  document.getElementById("transactionDetails").innerHTML = `
      <div class="alert alert-info">
        <h6><i class="fas fa-info-circle"></i> Transaction Details:</h6>
        <p><strong>Name:</strong> ${txn.name}</p>
        <p><strong>Amount:</strong> ₦${parseFloat(
          txn.amount || 0
        ).toLocaleString()}</p>
        <p><strong>Type:</strong> ${
          txn.type === "credit" ? "Credit (Contribution)" : "Debit (Loan)"
        }</p>
        <p><strong>Description:</strong> ${txn.description}</p>
      </div>
    `;

  memberResults.innerHTML = "";
  memberSearch.value = "";

  // Show candidate matches if available
  if (txn.candidate_matches && txn.candidate_matches.length) {
    memberResults.innerHTML =
      '<h6><i class="fas fa-users"></i> Candidate Matches:</h6>';
    txn.candidate_matches.forEach((candidate) => {
      const div = document.createElement("div");
      div.className = "match-item";
      div.innerHTML = `
          <div class="d-flex justify-content-between align-items-center">
            <span><strong>${candidate.name}</strong></span>
            <button class="btn btn-success btn-sm" onclick="selectMember('${candidate.memberid}', '${candidate.name}')">
              <i class="fas fa-check"></i> Select
            </button>
          </div>
        `;
      memberResults.appendChild(div);
    });
  }

  // Show the modal using Bootstrap
  const modalElement = document.getElementById("manualMatchModal");
  if (modalElement) {
    // Remove any existing backdrop first
    const existingBackdrops = document.querySelectorAll(".modal-backdrop");
    existingBackdrops.forEach((backdrop) => backdrop.remove());

    const modal = new bootstrap.Modal(modalElement, {
      backdrop: "static", // Prevent closing on backdrop click, but allow interaction
      keyboard: true,
      focus: true,
    });

    // Add event listeners for proper cleanup
    modalElement.addEventListener("hidden.bs.modal", function () {
      // Remove backdrop when modal is hidden
      const backdrops = document.querySelectorAll(".modal-backdrop");
      backdrops.forEach((backdrop) => backdrop.remove());

      // Re-enable body scrolling
      document.body.classList.remove("modal-open");
      document.body.style.overflow = "";
      document.body.style.paddingRight = "";
    });

    modal.show();
  }
};

// Search members
const memberSearch = document.getElementById("memberSearch");
const memberResults = document.getElementById("memberResults");

if (memberSearch) {
  memberSearch.addEventListener(
    "input",
    debounce(async () => {
      const query = memberSearch.value.trim();
      if (!query) {
        memberResults.innerHTML = "";
        return;
      }

      try {
        const response = await fetch("bank_statement_processor.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            action: "search_members",
            search_query: query,
          }),
        });
        const result = await response.json();
        memberResults.innerHTML = "";

        if (result.success && result.data.length) {
          result.data.forEach((member) => {
            const div = document.createElement("div");
            div.className = "match-item";
            div.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
              <span><strong>${member.name}</strong></span>
              <button class="btn btn-success btn-sm" onclick="selectMember('${member.member_id}', '${member.name}')">
                <i class="fas fa-check"></i> Select
              </button>
            </div>
          `;
            memberResults.appendChild(div);
          });
        } else {
          memberResults.innerHTML =
            '<p class="text-muted"><i class="fas fa-search"></i> No members found.</p>';
        }
      } catch (error) {
        memberResults.innerHTML = `<p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Search failed: ${error.message}</p>`;
      }
    }, 300)
  );
}

// Select member for manual matching
async function selectMember(memberId, memberName) {
  const index = currentTransactionIndex;
  const txn = allTransactions[index];

  try {
    console.log("Sending manual match request:", {
      action: "manual_match",
      transaction_name: txn.name,
      member_id: memberId,
    });

    const response = await fetch("bank_statement_processor.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        action: "manual_match",
        transaction_name: txn.name,
        member_id: memberId,
      }),
    });

    console.log("Response status:", response.status);
    console.log(
      "Response headers:",
      Object.fromEntries(response.headers.entries())
    );

    const responseText = await response.text();
    console.log("Raw response length:", responseText.length);
    console.log(
      "Raw response (first 500 chars):",
      responseText.substring(0, 500)
    );
    console.log(
      "Raw response (last 200 chars):",
      responseText.substring(Math.max(0, responseText.length - 200))
    );

    // Check if response is empty
    if (!responseText || responseText.trim() === "") {
      console.error("Empty response received from server");
      throw new Error(
        "Server returned empty response. Please check server logs."
      );
    }

    let result;
    try {
      result = JSON.parse(responseText);
    } catch (parseError) {
      console.error("JSON parse error:", parseError);
      console.error("Response text:", responseText);
      console.error("Response status:", response.status);
      console.error(
        "Response headers:",
        Object.fromEntries(response.headers.entries())
      );
      throw new Error(
        `Invalid JSON response: ${responseText.substring(0, 200)}...`
      );
    }

    console.log("Parsed result:", result);

    if (result.success) {
      allTransactions[index].matched = true;
      allTransactions[index].member_id = memberId;
      allTransactions[index].member_name = memberName;
      displayResults({ success: true, data: allTransactions });

      // Close modal and remove backdrop
      const modalElement = document.getElementById("manualMatchModal");
      if (modalElement) {
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
          modal.hide();
        } else {
          // Fallback: manually hide modal and remove backdrop
          modalElement.classList.remove("show");
          modalElement.style.display = "none";
          const backdrop = document.querySelector(".modal-backdrop");
          if (backdrop) {
            backdrop.remove();
          }
        }
      }

      // Remove any remaining backdrop
      const backdrops = document.querySelectorAll(".modal-backdrop");
      backdrops.forEach((backdrop) => backdrop.remove());

      // Re-enable body scrolling
      document.body.classList.remove("modal-open");
      document.body.style.overflow = "";
      document.body.style.paddingRight = "";

      // Show success message
      alert(`✅ Successfully matched "${txn.name}" with "${memberName}"`);
    } else {
      alert("Failed to save match: " + (result.message || "Unknown error"));
    }
  } catch (error) {
    console.error("Manual match error:", error);
    alert("Failed to save match: " + error.message);
  }
}

// Force remove all modal backdrops - make globally accessible
window.forceRemoveBackdrops = function () {
  const backdrops = document.querySelectorAll(".modal-backdrop");
  backdrops.forEach((backdrop) => {
    backdrop.remove();
    backdrop.style.display = "none";
  });

  // Re-enable body scrolling
  document.body.classList.remove("modal-open");
  document.body.style.overflow = "";
  document.body.style.paddingRight = "";
  document.body.style.pointerEvents = "";

  console.log("Backdrops removed, page interaction restored");
};

// Test manual match backend - make globally accessible
window.testManualMatch = async function () {
  try {
    console.log("Testing manual match backend...");
    const response = await fetch("bank_statement_processor.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        action: "manual_match",
        transaction_name: "TEST TRANSACTION",
        member_id: "1",
      }),
    });

    console.log("Test response status:", response.status);
    const responseText = await response.text();
    console.log("Test response text:", responseText);

    if (responseText.trim() === "") {
      alert("❌ Backend returned empty response. Check server logs.");
    } else {
      try {
        const result = JSON.parse(responseText);
        alert(`✅ Backend working: ${result.success ? "Success" : "Error"}`);
      } catch (e) {
        alert(
          `❌ Backend returned invalid JSON: ${responseText.substring(0, 100)}`
        );
      }
    }
  } catch (error) {
    console.error("Test error:", error);
    alert(`❌ Test failed: ${error.message}`);
  }
};

// Close manual match modal - make globally accessible
window.closeManualMatchModal = function () {
  const modalElement = document.getElementById("manualMatchModal");
  if (modalElement) {
    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) {
      modal.hide();
    } else {
      // Fallback: manually hide modal and remove backdrop
      modalElement.classList.remove("show");
      modalElement.style.display = "none";
    }
  }

  // Force remove all backdrops and restore page interaction
  setTimeout(() => {
    // Remove any remaining backdrop
    const backdrops = document.querySelectorAll(".modal-backdrop");
    backdrops.forEach((backdrop) => {
      backdrop.remove();
      backdrop.style.display = "none";
    });

    // Re-enable body scrolling
    document.body.classList.remove("modal-open");
    document.body.style.overflow = "";
    document.body.style.paddingRight = "";

    // Force enable page interaction
    document.body.style.pointerEvents = "";
  }, 100);
};

// Save manual match
async function saveManualMatch() {
  const selected = memberResults.querySelector(".btn-success.btn-sm.clicked");
  if (selected) {
    const memberId = selected.getAttribute("onclick").match(/'(\d+)'/)[1];
    const memberName = selected.parentElement.querySelector("span").textContent;
    await selectMember(memberId, memberName);
  } else {
    alert("Please select a member to match.");
  }
}

// Process selected transactions
async function processSelectedTransactions() {
  const selectedCheckboxes = document.querySelectorAll(
    ".transaction-checkbox:checked"
  );

  if (selectedCheckboxes.length === 0) {
    alert("Please select at least one transaction to process.");
    return;
  }

  const selectedTransactions = [];
  selectedCheckboxes.forEach((checkbox) => {
    const index = parseInt(checkbox.getAttribute("data-index"));
    const transaction = allTransactions[index];
    console.log(`Processing transaction ${index}:`, {
      name: transaction.name,
      type: transaction.type,
      amount: transaction.amount,
      matched: transaction.matched,
    });
    selectedTransactions.push(transaction);
  });

  const periodSelect = document.getElementById("periodSelect");
  const period = periodSelect ? periodSelect.value : "";

  if (!period) {
    alert("Please select a period.");
    return;
  }

  try {
    const response = await fetch("bank_statement_processor.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        action: "process_transactions",
        transactions: JSON.stringify(selectedTransactions),
        period: period,
      }),
    });
    const result = await response.json();

    if (result.success) {
      // Show detailed results
      let message = `✅ Successfully processed ${result.processed_count} transactions.`;
      if (result.skipped_count > 0) {
        message += `\n\n⏭️ ${result.skipped_count} transactions skipped (duplicates).`;
      }
      if (result.unmatched_count > 0) {
        message += `\n\n❌ ${result.unmatched_count} unmatched transactions were not processed.`;
      }

      alert(message);
      resetForm();
    } else {
      alert("Processing failed: " + result.message);
    }
  } catch (error) {
    alert("Processing failed: " + error.message);
  }
}

// Export results
async function exportResults() {
  if (!allTransactions.length) {
    alert("No results to export.");
    return;
  }

  const periodSelect = document.getElementById("periodSelect");
  const periodText = periodSelect.options[periodSelect.selectedIndex].text;

  try {
    const response = await fetch("bank_statement_processor.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        action: "export_results",
        results: JSON.stringify(allTransactions),
        period_text: periodText,
      }),
    });
    const result = await response.json();

    if (result.success) {
      const link = document.createElement("a");
      link.href = result.file_path;
      link.download = result.filename;
      link.click();
    } else {
      alert("Export failed: " + result.message);
    }
  } catch (error) {
    alert("Export failed: " + error.message);
  }
}

// Reset form
function resetForm() {
  const fileInput = document.getElementById("bankStatementFile");
  const uploadButton = document.getElementById("uploadButton");
  const resultsContainer = document.getElementById("resultsContainer");
  const periodSelect = document.getElementById("periodSelect");
  const clearDuplicateBtn = document.getElementById("clearDuplicateBtn");

  if (fileInput) fileInput.value = "";
  if (uploadButton) uploadButton.disabled = true;
  if (resultsContainer) resultsContainer.innerHTML = "";
  if (periodSelect) periodSelect.value = "";
  if (clearDuplicateBtn) clearDuplicateBtn.style.display = "none";

  allTransactions = [];
  currentTransactionIndex = null;
}

// Debounce function for search
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}
