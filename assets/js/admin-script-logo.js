// jQuery(document).ready(function ($) {
//   $("#upload_logo_button").click(function (e) {
//     e.preventDefault();

//     // Check if wp.media exists
//     if (typeof wp.media !== "undefined") {
//       var mediaUploader = wp.media({
//         title: "Select Business Logo",
//         button: { text: "Use this Logo" },
//         multiple: false,
//       });

//       mediaUploader.on("select", function () {
//         var attachment = mediaUploader
//           .state()
//           .get("selection")
//           .first()
//           .toJSON();
//         $("#wp_rental_business_logo").val(attachment.url); // Set input field
//         $("#logo_preview").attr("src", attachment.url).show(); // Show preview
//       });

//       mediaUploader.open();
//     } else {
//       console.error(
//         "wp.media is not available. Ensure wp_enqueue_media() is loaded."
//       );
//     }
//   });
// });
