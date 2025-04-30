jQuery(document).ready(function ($) {
  let mediaUploader;
  // let logoUploader;

  //   $("#upload_gallery_button").click(function (e) {
  //     e.preventDefault();
  //     if (mediaUploader) {
  //       mediaUploader.open();
  //       return;
  //     }

  //     mediaUploader = wp.media({
  //       title: "Select Gallery Images",
  //       button: { text: "Add to Gallery" },
  //       multiple: true,
  //     });

  //     mediaUploader.on("select", function () {
  //       let selection = mediaUploader.state().get("selection");
  //       let imageIds = [];
  //       let previewHtml = "";

  //       selection.each(function (attachment) {
  //         imageIds.push(attachment.id);
  //         previewHtml +=
  //           '<img src="' +
  //           attachment.attributes.url +
  //           '" style="width:100px; margin:5px;" />';
  //       });

  //       $("#gallery_images").val(imageIds.join(","));
  //       $("#gallery_preview").html(previewHtml);
  //     });

  //     mediaUploader.open();
  //   });

  // Open Media Library on Button Click

  $("#upload_gallery_button").click(function (e) {
    e.preventDefault();
    if (mediaUploader) {
      mediaUploader.open();
      return;
    }

    mediaUploader = wp.media({
      title: "Select Gallery Images",
      button: { text: "Add to Gallery" },
      multiple: true,
    });

    mediaUploader.on("select", function () {
      let selection = mediaUploader.state().get("selection");
      let imageIds = $("#gallery_images").val().split(",").filter(Boolean); // Existing Images
      let previewHtml = $("#gallery_preview").html(); // Keep previous images

      selection.each(function (attachment) {
        if (!imageIds.includes(attachment.id.toString())) {
          imageIds.push(attachment.id);
          previewHtml +=
            '<div class="gallery-item" data-id="' +
            attachment.id +
            '">' +
            '<img src="' +
            attachment.attributes.url +
            '" style="width:100px; margin:5px;" />' +
            '<button type="button" class="remove-image button">Remove</button>' +
            "</div>";
        }
      });

      $("#gallery_images").val(imageIds.join(","));
      $("#gallery_preview").html(previewHtml);
    });

    mediaUploader.open();
  });

  // Remove Image Button Click
  $("#gallery_preview").on("click", ".remove-image", function () {
    let imageId = $(this).parent().data("id");
    let imageIds = $("#gallery_images").val().split(",").filter(Boolean);
    let newImageIds = imageIds.filter((id) => id !== imageId.toString());

    $("#gallery_images").val(newImageIds.join(","));
    $(this).parent().remove();
  });

  // // Add the logo

  // if (document.getElementById("wp-rental-admin-settings")) {
  //   $("#upload_logo_button").click(function (e) {
  //     e.preventDefault();

  //     // Check if wp.media exists
  //     // if (typeof wp.media !== "undefined") {
  //     logoUploader = wp.media({
  //       title: "Select Business Logo",
  //       button: { text: "Use this Logo" },
  //       multiple: false,
  //     });

  //     logoUploader.on("select", function () {
  //       var attachment = logoUploader.state().get("selection").first().toJSON();
  //       $("#wp_rental_business_logo").val(attachment.url); // Set input field
  //       $("#logo_preview").attr("src", attachment.url).show(); // Show preview
  //     });

  //     logoUploader.open();
  //     // } else {
  //     //   console.error(
  //     //     "wp.media is not available. Ensure wp_enqueue_media() is loaded."
  //     //   );
  //     // }
  //   });
  // }

  //   Rental Pricing

  //   document.addEventListener("DOMContentLoaded", function () {
  const wrapper = document.getElementById("rental_pricing_wrapper");

  function updateAvailableDurations() {
    let selectedDurations = new Set();

    // Collect all selected durations
    wrapper.querySelectorAll("select").forEach((select) => {
      selectedDurations.add(select.value);
    });

    // Update dropdowns
    wrapper.querySelectorAll("select").forEach((select) => {
      select.querySelectorAll("option").forEach((option) => {
        option.disabled =
          selectedDurations.has(option.value) && option.value !== select.value;
      });
    });
  }

  if (document.getElementById("add_rental_pricing")) {
    document
      .getElementById("add_rental_pricing")
      .addEventListener("click", function () {
        const index = wrapper.children.length;
        const entry = document.createElement("div");
        entry.classList.add("rental_pricing_entry");

        entry.innerHTML = `
            <select name="rental_pricing[${index}][duration]" required>
                <option value="" disabled selected>Select Duration</option>
                <option value="1 hour">1 Hour</option>
                <option value="3 hours">3 Hours</option>
                <option value="6 hours">6 Hours</option>
                <option value="1 day">1 Day</option>
                <option value="3 days">3 Days</option>
                <option value="1 week">1 Week</option>
            </select>
            <input type="number" name="rental_pricing[${index}][cost]" step="0.01" min="0" required>
            <button type="button" class="remove_rental_pricing">❌</button>
        `;

        // entry.innerHTML = `
        //     <select name="rental_pricing[\${index}][duration]" required>
        //         <option value="" disabled selected>Select Duration</option>
        //         <option value="1 hour">1 Hour</option>
        //         <option value="3 hours">3 Hours</option>
        //         <option value="6 hours">6 Hours</option>
        //         <option value="1 day">1 Day</option>
        //         <option value="3 days">3 Days</option>
        //         <option value="1 week">1 Week</option>
        //     </select>
        //     <input type="number" name="rental_pricing[\${index}][cost]" step="0.01" min="0" required>
        //     <button type="button" class="remove_rental_pricing">❌</button>
        // `;

        wrapper.appendChild(entry);
        updateAvailableDurations();

        entry
          .querySelector(".remove_rental_pricing")
          .addEventListener("click", function () {
            entry.remove();
            updateAvailableDurations();
          });

        entry
          .querySelector("select")
          .addEventListener("change", updateAvailableDurations);
      });

    wrapper.querySelectorAll(".remove_rental_pricing").forEach((button) => {
      button.addEventListener("click", function () {
        this.parentElement.remove();
        updateAvailableDurations();
      });
    });

    wrapper.querySelectorAll("select").forEach((select) => {
      select.addEventListener("change", updateAvailableDurations);
    });

    updateAvailableDurations(); // Run initially to disable already selected options
  }

  if (document.getElementById("view-booking-details")) {
    $(".view-details").on("click", function () {
      let bookingId = $(this).data("booking-id");

      $.ajax({
        url: wvr_admin_vars.ajax_url, // This should be correctly set in WordPress
        type: "GET",
        data: {
          action: "get_booking_details",
          booking_id: bookingId,
          nonce: wvr_admin_vars.nonce,
        },
        success: function (response) {
          if (response.success) {
            let booking = response.data.booking;
            let client = response.data.client;
            let vehicle = response.data.vehicle;

            let actionsHtml = "";
            if (booking.status !== "completed") {
              actionsHtml += `<button class="mark-as-completed" data-booking-id="${bookingId}">Mark as Completed</button> `;
            }
            actionsHtml += `<button class="cancel-booking" data-booking-id="${bookingId}">Cancel Booking</button> `;
            actionsHtml += `<button class="delete-booking" data-booking-id="${bookingId}">Delete Booking</button> `;

            let modalHtml = `
                        <div id="bookingDetailsModal" class="booking-modal">
                            <div class="booking-modal-content">
                                <span class="close-modal">&times;</span>
                                <h2>Booking Details</h2>
                                <p><strong>Booking ID:</strong> ${booking.id}</p>
                                <p><strong>Booking Date:</strong> ${booking.booking_date}</p>
                                <p><strong>Status:</strong> ${booking.status}</p>
                                <p><strong>Created At:</strong> ${booking.created_at}</p>
                                <p><strong>Package:</strong> ${booking.package}</p>
                                <hr>

                                <h2>Client Details</h2>
                                <p><strong>Name:</strong> ${client.name}</p>
                                <p><strong>Email:</strong> ${client.email}</p>
                                <p><strong>Phone:</strong> ${client.phone}</p>
                                <p><strong>Address:</strong> ${client.address}</p>
                                <hr>

                                <h2>Vehicle Details</h2>
                                <p><strong>Vehicle Name:</strong> ${vehicle.name}</p>
                                <p><strong>Description:</strong> ${vehicle.description}</p>
                                <p><strong>Type:</strong> ${vehicle.type}</p>
                                <p><strong>Availability:</strong> ${vehicle.availability}</p>
                                <hr>

                                ${actionsHtml}
                            </div>
                        </div>
                    `;

            // Append modal HTML to body and display
            $("body").append(modalHtml);
            $("#bookingDetailsModal").fadeIn();

            // Close modal on click
            $(".close-modal").on("click", function () {
              $("#bookingDetailsModal").fadeOut(function () {
                $(this).remove();
              });
            });
          } else {
            alert("Failed to retrieve booking details.");
          }
        },
        error: function () {
          alert("An error occurred. Please try again.");
        },
      });
    });

    $(document).on(
      "click",
      ".mark-paid, .mark-completed, .cancel-booking, .delete-booking, .mark-as-completed",
      function () {
        let button = $(this);
        let action = button.hasClass("mark-paid")
          ? "mark_as_paid"
          : button.hasClass("mark-as-completed")
          ? "mark_as_completed"
          : button.hasClass("cancel-booking")
          ? "cancel_booking"
          : button.hasClass("delete-booking")
          ? "delete_booking"
          : "";

        let bookingId = button.data("booking-id"); // Ensure button has data-booking-id attribute

        if (!action || !bookingId) {
          alert("Invalid action or booking ID missing.");
          return;
        }

        // Confirm actions where needed
        if (
          action === "delete_booking" &&
          !confirm("Are you sure you want to delete this booking?")
        ) {
          return;
        }

        // console.log({
        //   action: action,
        //   booking_id: bookingId,
        //   nonce: wvr_admin_vars.nonce,
        // });

        $.ajax({
          url: wvr_admin_vars.ajax_url, // Defined in wp_localize_script
          type: "POST",
          dataType: "json",
          data: {
            action: action,
            booking_id: bookingId,
            nonce: wvr_admin_vars.nonce,
          },
          beforeSend: function () {
            button.prop("disabled", true).text("Processing...");
          },
          success: function (response) {
            button
              .prop("disabled", false)
              .text(response.data.new_label || button.text());
            console.log(response);
            if (response.success) {
              alert(response.data.message);
              location.reload(); // Refresh the page or update UI dynamically
            } else {
              alert(response.data.message);
            }
          },
          error: function () {
            button.prop("disabled", false).text("Try Again");
            alert("Something went wrong. Please try again.");
          },
        });
      }
    );
  }

  if (document.getElementById("payment-details-modal")) {
    $(document).on("click", ".view-payment-details", function (e) {
      e.preventDefault();

      let paymentId = $(this).data("payment-id");

      $.ajax({
        url: wvr_admin_vars.ajax_url, // WordPress AJAX URL
        type: "POST",
        data: {
          action: "get_payment_details",
          payment_id: paymentId,
        },
        beforeSend: function () {
          $("#payment-details-modal .modal-content").html("<p>Loading...</p>");
          $("#payment-details-modal").show();
        },
        success: function (response) {
          if (response.success) {
            let data = response.data;

            let actionsHtml = "";
            if (data.payment_status !== "Paid") {
              actionsHtml += `<button class="mark-as-paid" data-payment-id="${paymentId}">Mark as Paid</button> `;
            }
            if (data.payment_status === "Paid") {
              actionsHtml += `<button class="process-refund" data-payment-id="${paymentId}">Process Refund</button>`;
            }

            let modalHtml = `
                      <div id="paymentDetailsModal" class="booking-modal">
                          <div class="booking-modal-content">
                              <span class="close-modal">&times;</span>
                              <h2>Payment Details</h2>
                              <p><strong>Order ID:</strong> ${data.order_id}</p>
                              <p><strong>Client Name:</strong> ${data.client_name}</p>
                              <p><strong>Vehicle:</strong> ${data.vehicle_name}</p>
                              <p><strong>Booking Date:</strong> ${data.booking_date}</p>
                              <p><strong>Booking Status:</strong> ${data.booking_status}</p>
                              <p><strong>Amount:</strong> ${data.currency} ${data.amount}</p>
                              <p><strong>Payment Method:</strong> ${data.payment_method}</p>
                              <p><strong>Transaction ID:</strong> ${data.transaction_id}</p>
                              <p><strong>Payment Status:</strong> ${data.payment_status}</p>

                              <div class="payment-actions">${actionsHtml}</div>
                          </div>
                      </div>
                  `;

            // Remove any existing modal before adding a new one
            $("#paymentDetailsModal").remove();
            $("body").append(modalHtml);
            $("#paymentDetailsModal").fadeIn();
          } else {
            alert("Failed to retrieve payment details.");
          }
        },
        error: function () {
          alert("An error occurred.");
        },
      });
    });

    // Close modal when clicking the close button
    $(document).on("click", ".close-modal", function () {
      $("#paymentDetailsModal").fadeOut(function () {
        $(this).remove();
      });
    });

    // Handle "Mark as Paid" button click
    $(document).on("click", ".mark-as-paid", function () {
      let paymentId = $(this).data("payment-id");

      $.ajax({
        url: wvr_admin_vars.ajax_url,
        type: "POST",
        data: {
          action: "mark_payment_as_paid",
          payment_id: paymentId,
        },
        success: function (response) {
          if (response.success) {
            alert("Payment marked as paid successfully!");
            $("#paymentDetailsModal").fadeOut(function () {
              $(this).remove();
            });
            location.reload();
          } else {
            alert("Error: " + response.data.message);
          }
        },
        error: function () {
          alert("An error occurred.");
        },
      });
    });

    // Handle "Process Refund" button click
    $(document).on("click", ".process-refund", function () {
      let paymentId = $(this).data("payment-id");

      if (!confirm("Are you sure you want to process a refund?")) return;

      $.ajax({
        url: wvr_admin_vars.ajax_url,
        type: "POST",
        data: {
          action: "process_payment_refund",
          payment_id: paymentId,
        },
        success: function (response) {
          if (response.success) {
            alert("Refund processed successfully!");
            $("#paymentDetailsModal").fadeOut(function () {
              $(this).remove();
            });
            location.reload();
          } else {
            alert("Error: " + response.data.message);
          }
        },
        error: function () {
          alert("An error occurred.");
        },
      });
    });
  }

  if (document.getElementById("wp-rental-admin-settings")) {
    $("#upload_logo_button").click(function (e) {
      e.preventDefault();

      // Check if wp.media exists
      if (typeof wp.media !== "undefined") {
        var mediaUploader = wp.media({
          title: "Select Business Logo",
          button: { text: "Use this Logo" },
          multiple: false,
        });

        mediaUploader.on("select", function () {
          var attachment = mediaUploader
            .state()
            .get("selection")
            .first()
            .toJSON();
          $("#wp_rental_business_logo").val(attachment.url); // Set input field
          $("#logo_preview").attr("src", attachment.url).show(); // Show preview
        });

        mediaUploader.open();
      } else {
        console.error(
          "wp.media is not available. Ensure wp_enqueue_media() is loaded."
        );
      }
    });
  }
});
