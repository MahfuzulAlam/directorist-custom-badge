/**
 * CSV Download Functionality
 * Handles CSV download for dashboard listings
 */

jQuery(document).ready(function ($) {
  // CSV Download functionality - Use event delegation to prevent multiple handlers
  $(document).off('click', '.publishing-directory-dashboard-csv-download').on('click', '.publishing-directory-dashboard-csv-download', function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    
    const $button = $(this);
    
    // Prevent multiple clicks during download
    if ($button.prop('disabled') || $button.hasClass('downloading')) {
      return false;
    }
    
    const originalText = $button.text();
    
    // Show loading state
    $button.text('Downloading...').prop('disabled', true).addClass('downloading');
    
    // Create form data
    const formData = new FormData();
    formData.append('action', 'publishing_directory_download_csv');
    formData.append('nonce', publishing_directory_ajax.nonce);
    
    // Make AJAX request
    $.ajax({
      url: publishing_directory_ajax.ajax_url,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        // Create download link
        const blob = new Blob([response], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'my-listings-' + new Date().toISOString().slice(0, 19).replace(/:/g, '-') + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        // Reset button after a short delay
        setTimeout(function() {
          $button.text(originalText).prop('disabled', false).removeClass('downloading');
        }, 1000);
      },
      error: function(xhr, status, error) {
        console.error('CSV download failed:', error);
        alert('Failed to download CSV. Please try again.');
        
        // Reset button
        $button.text(originalText).prop('disabled', false).removeClass('downloading');
      }
    });
  });
});
