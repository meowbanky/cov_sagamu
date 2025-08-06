// Client-side PDF Text Extractor using pdf.js
class PDFTextExtractor {
    constructor() {
        this.pdfjsLib = window['pdfjs-dist/build/pdf'];
        this.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    }

    async extractTextFromFile(file) {
        try {
            const arrayBuffer = await this.readFileAsArrayBuffer(file);
            const pdf = await this.pdfjsLib.getDocument({ data: arrayBuffer }).promise;
            let fullText = '';

            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                const page = await pdf.getPage(pageNum);
                const textContent = await page.getTextContent();
                const pageText = textContent.items.map(item => item.str).join(' ');
                fullText += pageText + '\n';
            }

            return fullText.trim();
        } catch (error) {
            console.error('PDF text extraction error:', error);
            throw new Error('Failed to extract text from PDF: ' + error.message);
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