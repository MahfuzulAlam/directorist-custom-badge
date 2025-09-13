/**
 * Custom Badge Fields Toggle
 * Shows/hides textarea and file upload fields based on checkbox selection
 */

jQuery(document).ready(function ($) {
  // Cache DOM elements for better performance
  const $container = $('#add-listing-content-user-requests-badges');
  const $checkboxes = $container.find('input[name="qualified_badges[]"]');
  const $fields = $container.find('.directorist-custom-field-textarea, .directorist-custom-field-file-upload');
  
  // Toggle function
  function toggleFields() {
    const hasCheckedBoxes = $checkboxes.filter(':checked').length > 0;
    $fields.toggle(hasCheckedBoxes);
  }
  
  // Initialize on page load
  toggleFields();
  
  // Handle checkbox changes
  $checkboxes.on('change', toggleFields);
});
