// Client-side PDF Text Extractor using pdf.js
class PDFTextExtractor {
  constructor() {
    this.pdfjsLib = window["pdfjs-dist/build/pdf"];
    this.pdfjsLib.GlobalWorkerOptions.workerSrc =
      "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js";
  }

  async extractTextFromFile(file, password = null) {
    try {
      const arrayBuffer = await this.readFileAsArrayBuffer(file);
      const options = { data: arrayBuffer };

      if (password) {
        options.password = password;
      }

      const pdf = await this.pdfjsLib.getDocument(options).promise;
      let fullText = "";

      for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
        const page = await pdf.getPage(pageNum);
        const textContent = await page.getTextContent();
        const pageText = textContent.items.map((item) => item.str).join(" ");
        fullText += pageText + "\n";
      }

      return fullText.trim();
    } catch (error) {
      console.error("PDF text extraction error:", error);
      if (
        error.name === "PasswordException" ||
        error.message.includes("password")
      ) {
        throw new Error(
          "PDF is password-protected. Please provide the correct password."
        );
      }
      throw new Error("Failed to extract text from PDF: " + error.message);
    }
  }

  async extractTextByPages(file, password = null) {
    try {
      const arrayBuffer = await this.readFileAsArrayBuffer(file);
      const options = { data: arrayBuffer };

      if (password) {
        options.password = password;
      }

      const pdf = await this.pdfjsLib.getDocument(options).promise;
      const pages = [];

      for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
        const page = await pdf.getPage(pageNum);
        const textContent = await page.getTextContent();
        const pageText = textContent.items.map((item) => item.str).join(" ");

        // Include positioning information for table detection
        const items = textContent.items.map((item) => ({
          text: item.str,
          x: item.transform[4],
          y: item.transform[5],
          width: item.width,
          height: item.height,
        }));

        pages.push({
          pageNumber: pageNum,
          text: pageText.trim(),
          fileName: `${file.name}_page_${pageNum}.pdf`,
          items: items,
        });
      }

      return pages;
    } catch (error) {
      console.error("PDF page extraction error:", error);
      if (error.name === "PasswordException") {
        if (error.code === 1) {
          throw new Error(
            "PDF is password-protected. Please provide a password."
          );
        } else if (error.code === 2) {
          throw new Error(
            "Incorrect password provided. Please check your password and try again."
          );
        } else {
          throw new Error("Password error: " + error.message);
        }
      } else if (error.message.includes("password")) {
        throw new Error(
          "PDF is password-protected. Please provide the correct password."
        );
      }
      throw new Error("Failed to extract pages from PDF: " + error.message);
    }
  }

  async createPageBlob(file, pageNumber) {
    try {
      const arrayBuffer = await this.readFileAsArrayBuffer(file);
      const pdf = await this.pdfjsLib.getDocument({ data: arrayBuffer })
        .promise;

      // Create a new PDF document with just this page
      const pdfDoc = await this.pdfjsLib.PDFDocument.create();
      const [copiedPage] = await pdfDoc.copyPages(pdf, [pageNumber - 1]);
      pdfDoc.addPage(copiedPage);

      const pdfBytes = await pdfDoc.save();
      return new Blob([pdfBytes], { type: "application/pdf" });
    } catch (error) {
      console.error("PDF page blob creation error:", error);
      throw new Error("Failed to create page blob: " + error.message);
    }
  }

  readFileAsArrayBuffer(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => resolve(reader.result);
      reader.onerror = () => reject(reader.error);
      reader.readAsArrayBuffer(file);
    });
  }
}

// Global PDF extractor instance
window.pdfTextExtractor = new PDFTextExtractor();
