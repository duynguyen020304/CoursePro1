import { useState, useEffect } from 'react';
import { orderApi } from '../../services/api';
import jsPDF from 'jspdf';

interface Certificate {
  certificate_id: string;
  course_id: string | number;
  course_name: string;
  student_name: string;
  completion_date: string;
  certificate_url: string;
}

interface CertificateProps {
  certificate_id: string;
  course_name: string;
  student_name: string;
  completion_date: string;
}

export default function Certificates() {
  const [certificates, setCertificates] = useState<Certificate[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedCertificate, setSelectedCertificate] = useState<Certificate | null>(null);
  const [generatingPdf, setGeneratingPdf] = useState(false);

  useEffect(() => {
    async function fetchCertificates() {
      try {
        const response = await orderApi.list();
        // Handle paginated response - data.data.data is the orders array
        const orders = response.data.data?.data || response.data.data || [];

        const completedOrders = orders
          .filter((order: { status?: string }) => order.status === 'completed')
          .map((order: {
            order_id?: string;
            course?: { course_id?: string | number; title?: string };
            details?: Array<{ course_id?: string | number; course?: { title?: string } }>;
            user?: { first_name?: string; last_name?: string };
            created_at?: string;
          }) => ({
            certificate_id: `CERT-${order.order_id?.substring(0, 8).toUpperCase() || 'UNKNOWN'}`,
            course_id: order.details?.[0]?.course_id || order.course?.course_id,
            course_name: order.details?.[0]?.course?.title || order.course?.title || 'Unknown Course',
            student_name: `${order.user?.first_name || ''} ${order.user?.last_name || ''}`.trim() || 'Student',
            completion_date: order.created_at,
            certificate_url: `/certificates/${order.details?.[0]?.course_id || order.course?.course_id}`,
          }));

        setCertificates(completedOrders);
      } catch (error) {
        console.error('Failed to fetch certificates:', error);
      } finally {
        setLoading(false);
      }
    }
    fetchCertificates();
  }, []);

  const generatePDF = async (cert: CertificateProps) => {
    setGeneratingPdf(true);
    try {
      const doc = new jsPDF({
        orientation: 'landscape',
        unit: 'mm',
        format: 'a4',
      });

      const width = doc.internal.pageSize.getWidth();
      const height = doc.internal.pageSize.getHeight();

      // Background gradient
      const gradient = (doc as any).createLinearGradient(0, 0, width, height);
      gradient.addColorStop(0, '#4f46e5');
      gradient.addColorStop(1, '#7c3aed');
      doc.setFillColor(gradient);
      doc.rect(0, 0, width, height, 'F');

      // Border frame
      doc.setDrawColor(255, 255, 255);
      doc.setLineWidth(2);
      doc.rect(10, 10, width - 20, height - 20);

      // Decorative corners
      doc.setFillColor(255, 215, 0);
      doc.circle(15, 15, 5, 'F');
      doc.circle(width - 15, 15, 5, 'F');
      doc.circle(15, height - 15, 5, 'F');
      doc.circle(width - 15, height - 15, 5, 'F');

      // Title
      doc.setTextColor(255, 255, 255);
      doc.setFontSize(36);
      doc.setFont('helvetica', 'bold');
      doc.text('Certificate of Completion', width / 2, 40, { align: 'center' });

      // Subtitle
      doc.setFontSize(16);
      doc.setFont('helvetica', 'normal');
      doc.text('This is to certify that', width / 2, 55, { align: 'center' });

      // Student Name
      doc.setFontSize(28);
      doc.setFont('helvetica', 'bold');
      doc.setTextColor(255, 215, 0);
      doc.text(cert.student_name, width / 2, 75, { align: 'center' });

      // Course text
      doc.setFontSize(16);
      doc.setFont('helvetica', 'normal');
      doc.setTextColor(255, 255, 255);
      doc.text('has successfully completed the course', width / 2, 90, { align: 'center' });

      // Course Name
      doc.setFontSize(22);
      doc.setFont('helvetica', 'bold');
      doc.text(cert.course_name, width / 2, 105, { align: 'center' });

      // Date
      doc.setFontSize(14);
      doc.setFont('helvetica', 'normal');
      const formattedDate = new Date(cert.completion_date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      });
      doc.text(`Completed on: ${formattedDate}`, width / 2, 125, { align: 'center' });

      // Certificate ID
      doc.setFontSize(10);
      doc.setTextColor(200, 200, 200);
      doc.text(`Certificate ID: ${cert.certificate_id}`, width / 2, 145, { align: 'center' });

      // Signature line
      doc.setDrawColor(255, 255, 255);
      doc.setLineWidth(1);
      doc.line(40, 135, 100, 135);
      doc.line(width - 100, 135, width - 40, 135);

      doc.setFontSize(12);
      doc.text('Instructor Signature', 70, 145, { align: 'center' });
      doc.text('Director Signature', width - 70, 145, { align: 'center' });

      // Save PDF
      doc.save(`certificate-${cert.certificate_id}.pdf`);
    } catch (error) {
      console.error('Failed to generate PDF:', error);
    } finally {
      setGeneratingPdf(false);
    }
  };

  const openPreview = (cert: Certificate) => {
    setSelectedCertificate(cert);
  };

  const closePreview = () => {
    setSelectedCertificate(null);
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-900 mb-8">My Certificates</h1>

      {certificates.length === 0 ? (
        <div className="text-center py-12">
          <div className="text-6xl mb-4">📜</div>
          <p className="text-gray-500 text-lg mb-4">
            You haven&apos;t earned any certificates yet.
          </p>
          <p className="text-gray-600 mb-8">
            Complete courses to earn your certificates!
          </p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {certificates.map((cert) => (
            <div key={cert.certificate_id} className="bg-white rounded-xl shadow-md overflow-hidden">
              <div className="bg-gradient-to-r from-indigo-600 to-purple-600 p-4 text-white text-center relative">
                <div className="text-5xl mb-2">🎓</div>
                <h3 className="font-bold text-lg">Certificate of Completion</h3>
                <div className="absolute top-2 right-2 bg-white/20 px-2 py-1 rounded text-xs">
                  {cert.certificate_id}
                </div>
              </div>
              <div className="p-4">
                <h4 className="font-semibold text-gray-900 mb-2 line-clamp-2">
                  {cert.course_name}
                </h4>
                <p className="text-sm text-gray-500 mb-2">
                  Student: {cert.student_name}
                </p>
                <p className="text-sm text-gray-500 mb-4">
                  Issued on {new Date(cert.completion_date).toLocaleDateString()}
                </p>
                <div className="flex gap-2">
                  <button
                    onClick={() => openPreview(cert)}
                    className="flex-1 bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 text-sm flex items-center justify-center gap-1"
                  >
                    <span>👁️</span> Preview
                  </button>
                  <button
                    onClick={() => generatePDF(cert)}
                    disabled={generatingPdf}
                    className="flex-1 border border-indigo-600 text-indigo-600 py-2 rounded-lg hover:bg-indigo-50 text-sm flex items-center justify-center gap-1 disabled:opacity-50"
                  >
                    <span>📥</span> {generatingPdf ? 'Generating...' : 'Download'}
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Certificate Preview Modal */}
      {selectedCertificate && (
        <div className="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl max-w-4xl w-full max-h-[90vh] overflow-auto">
            <div className="flex justify-between items-center p-4 border-b">
              <h3 className="text-lg font-bold text-gray-900">Certificate Preview</h3>
              <button
                onClick={closePreview}
                className="text-gray-500 hover:text-gray-700 text-2xl"
              >
                x
              </button>
            </div>

            <div className="p-6">
              {/* Certificate Display */}
              <div className="relative bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-700 rounded-lg p-8 text-white">
                {/* Decorative border */}
                <div className="absolute inset-4 border-2 border-white/30 rounded-lg pointer-events-none"></div>

                {/* Corner decorations */}
                <div className="absolute top-6 left-6 w-8 h-8 bg-yellow-400 rounded-full opacity-80"></div>
                <div className="absolute top-6 right-6 w-8 h-8 bg-yellow-400 rounded-full opacity-80"></div>
                <div className="absolute bottom-6 left-6 w-8 h-8 bg-yellow-400 rounded-full opacity-80"></div>
                <div className="absolute bottom-6 right-6 w-8 h-8 bg-yellow-400 rounded-full opacity-80"></div>

                <div className="text-center relative z-10">
                  <div className="text-6xl mb-4">🎓</div>
                  <h2 className="text-3xl font-bold mb-2">Certificate of Completion</h2>
                  <p className="text-lg mb-6 opacity-90">This is to certify that</p>

                  <p className="text-3xl font-bold text-yellow-300 mb-6">
                    {selectedCertificate.student_name}
                  </p>

                  <p className="text-lg mb-2 opacity-90">has successfully completed the course</p>
                  <p className="text-2xl font-bold mb-6">{selectedCertificate.course_name}</p>

                  <p className="text-sm opacity-75 mb-8">
                    Completed on {new Date(selectedCertificate.completion_date).toLocaleDateString('en-US', {
                      year: 'numeric',
                      month: 'long',
                      day: 'numeric',
                    })}
                  </p>

                  <div className="flex justify-around items-end mt-8 pt-4">
                    <div className="text-center">
                      <div className="border-t border-white/50 pt-2 w-32 mx-auto text-sm">Instructor</div>
                    </div>
                    <div className="text-4xl">🖋️</div>
                    <div className="text-center">
                      <div className="border-t border-white/50 pt-2 w-32 mx-auto text-sm">Director</div>
                    </div>
                  </div>

                  <p className="text-xs opacity-50 mt-8">
                    Certificate ID: {selectedCertificate.certificate_id}
                  </p>
                </div>
              </div>

              <div className="flex gap-4 mt-6">
                <button
                  onClick={() => generatePDF(selectedCertificate)}
                  disabled={generatingPdf}
                  className="flex-1 bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 font-medium disabled:opacity-50 flex items-center justify-center gap-2"
                >
                  <span>📥</span> {generatingPdf ? 'Generating PDF...' : 'Download PDF'}
                </button>
                <button
                  onClick={closePreview}
                  className="flex-1 border border-gray-300 text-gray-700 py-3 rounded-lg hover:bg-gray-50 font-medium"
                >
                  Close
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
