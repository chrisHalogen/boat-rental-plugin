jQuery(document).ready(function ($) {
  if (document.getElementById("register-form")) {
    // console.log("Active");

    // Activation function

    document
      .getElementById("register-form")
      .addEventListener("submit", function (e) {
        e.preventDefault(); // Prevent default form submission

        let name = document.getElementById("name").value.trim();
        let email = document.getElementById("email").value.trim();
        let phone = document.getElementById("phone").value.trim();
        let password = document.getElementById("password").value;
        let confirmPassword = document.getElementById("re-password").value;
        let messageBox = document.getElementById("response-message");
        let nonce = document.getElementById("custom_register_nonce").value;

        // Simple validation
        if (!name || !email || !phone || !password || !confirmPassword) {
          messageBox.innerHTML =
            "<p style='color: red;'>All fields are required.</p>";
          return;
        }

        if (!nonce) {
          messageBox.innerHTML =
            "<p style='color: red;'>Something went wrong.</p>";
          return;
        }

        if (!/\S+@\S+\.\S+/.test(email)) {
          messageBox.innerHTML =
            "<p style='color: red;'>Invalid email format.</p>";
          return;
        }
        if (password !== confirmPassword) {
          messageBox.innerHTML =
            "<p style='color: red;'>Passwords do not match.</p>";
          return;
        }

        // Prepare data for AJAX
        let formData = new FormData();
        formData.append("action", "custom_register");
        formData.append("name", name);
        formData.append("email", email);
        formData.append("phone", phone);
        formData.append("password", password);
        formData.append("custom_register_nonce", nonce);

        // Send AJAX request
        fetch(wvr_frontend_vars.ajax_url, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              messageBox.innerHTML = `<p style='color: green;'>${data.message}</p>`;
            } else {
              messageBox.innerHTML = `<p style='color: red;'>${data.message}</p>`;
            }
            if (data.redirect) {
              setTimeout(() => {
                window.location.href = data.redirect;
              }, 5000); // Redirect after 5 seconds
            }
          })
          .catch((error) => console.error("Error:", error));
      });

    function activateUser(activationKey, userId) {
      fetch(wvr_frontend_vars.ajax_url, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=activate_user&activation_key=${activationKey}&user_id=${userId}`,
      })
        .then((response) => response.json())
        .then((data) => {
          let messageBox = document.getElementById("response-message");
          if (data.success) {
            messageBox.innerHTML = `<p style='color: green;'>${data.message}</p>`;
            setTimeout(() => {
              window.location.href = wvr_frontend_vars.login_page;
            }, 5000); // Redirect to login after 5 seconds
          } else {
            messageBox.innerHTML = `<p style='color: red;'>${data.message}</p>`;
          }
        });
    }

    // Check for activation params in URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has("activation_key") && urlParams.has("user_id")) {
      document.getElementById("register-form").style.display = "none";
      document.getElementById("welcome-message").style.display = "none";
      document.getElementById(
        "response-message"
      ).innerHTML = `<p>Activating your account...</p>`;
      activateUser(urlParams.get("activation_key"), urlParams.get("user_id"));
    }
  }

  if (document.getElementById("account-recovery")) {
    document
      .getElementById("recover-form")
      ?.addEventListener("submit", function (event) {
        event.preventDefault();
        let email = document.getElementById("email").value;
        let nonce = document.getElementById("account_recovery_nonce").value;
        let responseMessage = document.getElementById("response-message");
        responseMessage.innerHTML = "Processing...";

        fetch(wvr_frontend_vars.ajax_url, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({
            action: "recover_account",
            email: email,
            security: nonce,
          }),
        })
          .then((response) => response.json())
          .then((data) => {
            // console.log(data);
            responseMessage.innerHTML = `<p style="font-weight:bold; color: ${
              data.success ? "green" : "red"
            }">${data.data.message}</p>`;
          });
      });

    document
      .getElementById("reset-form")
      ?.addEventListener("submit", function (event) {
        event.preventDefault();
        let password = document.getElementById("password").value;
        let confirmPassword = document.getElementById("confirm-password").value;
        let nonce = document.getElementById("reset_password_nonce").value;
        let userId = document.querySelector("input[name='user_id']").value;
        let token = document.querySelector("input[name='token']").value;
        let responseMessage = document.getElementById("response-message");
        responseMessage.innerHTML = "Processing...";

        if (password !== confirmPassword) {
          responseMessage.innerHTML = "Passwords do not match!";
          return;
        }

        fetch(wvr_frontend_vars.ajax_url, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({
            action: "reset_password",
            password: password,
            confirm_password: confirmPassword,
            user_id: userId,
            token: token,
            security: nonce,
          }),
        })
          .then((response) => response.json())
          .then((data) => {
            // console.log(data);
            responseMessage.innerHTML = `<p style="font-weight:bold; color: ${
              data.success ? "green" : "red"
            }">${data.data.message}</p>`;
            if (data.success) {
              setTimeout(() => {
                window.location.href = wvr_frontend_vars.login_page;
              }, 5000);
            }
          });
      });
  }

  if (document.getElementById("client-login-form")) {
    // Handle login form submission
    $("#login-form").on("submit", function (event) {
      event.preventDefault();

      // Clear previous response messages
      $("#response-message").html("").removeClass("success error");

      // Get form data
      let email = $("#email").val();
      let password = $("#password").val();
      let nonce = $("#custom_login_nonce").val();

      // Basic validation
      if (!email || !password) {
        $("#response-message")
          .html(
            "<p style='font-weight:bold;color:red'>Please fill in all fields.</p>"
          )
          .addClass("error");
        return;
      }

      // Show loading message
      $("#response-message")
        .html("<p style='font-weight:bold;color:green'>Logging in...</p>")
        .addClass("success");

      // Send AJAX request
      $.ajax({
        url: wvr_frontend_vars.ajax_url, // WordPress AJAX URL
        type: "POST",
        data: {
          action: "custom_login", // PHP action hook
          email: email,
          password: password,
          security: nonce,
        },
        success: function (response) {
          if (response.success) {
            // Login successful
            $("#response-message")
              .html(
                `<p style='font-weight:bold;color:green'>${response.data.message}</p>`
              )
              .addClass("success");

            // Redirect to dashboard after 5 seconds
            setTimeout(function () {
              window.location.href = response.data.redirect;
            }, 5000);
          } else {
            // Login failed
            $("#response-message")
              .html(
                `<p style='font-weight:bold;color:red'>${response.data.message}</p>`
              )
              .addClass("error");
          }
        },
        error: function () {
          $("#response-message")
            .html(
              "<p style='font-weight:bold;color:red'>An error occurred. Please try again.</p>"
            )
            .addClass("error");
        },
      });
    });
  }

  if (document.getElementById("dashboard-outter-container")) {
    // Toggle mobile menu
    $("#toggle1, #toggle2").on("click", function () {
      $("#dashboard-mobile-navigation").toggleClass("open");
    });

    // Home link popup
    $("#home-link").on("click", function (e) {
      e.preventDefault();
      $("#home-popup").fadeIn();
    });

    $("#confirm-home").on("click", function () {
      window.location.href = wvr_frontend_vars.home_url;
    });

    $("#cancel-home").on("click", function () {
      $("#home-popup").fadeOut();
    });

    // Logout link popup
    $("#logout-link").on("click", function (e) {
      e.preventDefault();
      $("#logout-popup").fadeIn();
    });

    $("#confirm-logout").on("click", function () {
      window.location.href = wvr_frontend_vars.logout_url;
    });

    $("#cancel-logout").on("click", function () {
      $("#logout-popup").fadeOut();
    });
  }

  if (document.getElementById("dashboard-outter-container")) {
    const images = [
      { src: `${wvr_frontend_vars.img_path}/dsmc-h2.png`, zoom: "zoom-in" },
      { src: `${wvr_frontend_vars.img_path}/dsmc-h4.png`, zoom: "zoom-out" },
      { src: `${wvr_frontend_vars.img_path}/dsmc-h6.png`, zoom: "zoom-in" },
      { src: `${wvr_frontend_vars.img_path}/dsmc-h7.png`, zoom: "zoom-out" },
      { src: `${wvr_frontend_vars.img_path}/dsmc-h8.png`, zoom: "zoom-in" },
      { src: `${wvr_frontend_vars.img_path}/dsmc-h12.png`, zoom: "zoom-out" },
    ];

    const backgroundContainer = document.getElementById(
      "dashboard-outter-container"
    );
    let currentIndex = 0;

    function changeBackground() {
      // Remove active class from current image
      const currentImage = backgroundContainer.querySelector("img.active");
      if (currentImage) {
        currentImage.classList.remove("active", currentImage.dataset.zoom);
      }

      // Move to the next image
      currentIndex = (currentIndex + 1) % images.length;
      const nextImage = backgroundContainer.querySelector(
        `img[src="${images[currentIndex].src}"]`
      );

      // Add active class and zoom effect to the next image
      nextImage.classList.add("active", images[currentIndex].zoom);
    }

    // Preload images and append them to the container
    images.forEach((image, index) => {
      const imgElement = document.createElement("img");
      imgElement.src = image.src;
      imgElement.dataset.zoom = image.zoom;
      if (index === 0) {
        imgElement.classList.add("active", image.zoom);
      }
      backgroundContainer.appendChild(imgElement);
    });

    // Change background every 5 seconds
    setInterval(changeBackground, 5000);
  }

  if (document.getElementById("account-tab-container")) {
    // Edit Profile Popup
    $("#edit-profile-btn").on("click", function () {
      $("#edit-profile-popup").fadeIn();
    });

    $("#cancel-edit-profile").on("click", function () {
      $("#edit-profile-popup").fadeOut();
    });

    // Change Password Popup
    $("#change-password-btn").on("click", function () {
      $("#change-password-popup").fadeIn();
    });

    $("#cancel-change-password").on("click", function () {
      $("#change-password-popup").fadeOut();
    });

    // Delete Account Popup
    $("#delete-account-btn").on("click", function () {
      $("#delete-account-popup").fadeIn();
    });

    $("#cancel-delete-account").on("click", function () {
      $("#delete-account-popup").fadeOut();
    });

    // Handle Edit Profile Form Submission
    $("#edit-profile-form").on("submit", function (e) {
      e.preventDefault();
      const formData = $(this).serialize();

      $.ajax({
        url: wvr_frontend_vars.ajax_url,
        type: "POST",
        data: {
          action: "update_profile",
          data: formData,
        },
        success: function (response) {
          if (response.success) {
            location.reload(); // Reload the page to reflect changes
          } else {
            alert("Error: " + response.data);
          }
        },
      });
    });

    // Handle Change Password Form Submission
    $("#change-password-form").on("submit", function (e) {
      e.preventDefault();
      const formData = $(this).serialize();

      $.ajax({
        url: wvr_frontend_vars.ajax_url,
        type: "POST",
        data: {
          action: "change_password",
          data: formData,
        },
        success: function (response) {
          if (response.success) {
            alert("Password changed successfully.");
            $("#change-password-popup").fadeOut();
          } else {
            alert("Error: " + response.data);
          }
        },
      });
    });

    // Handle Delete Account Confirmation
    $("#confirm-delete-account").on("click", function () {
      $.ajax({
        url: wvr_frontend_vars.ajax_url,
        type: "POST",
        data: {
          action: "delete_account",
        },
        success: function (response) {
          if (response.success) {
            window.location.href = wvr_frontend_vars.home_url;
          } else {
            alert("Error: " + response.data);
          }
        },
      });
    });
  }

  if (document.getElementById("support-container")) {
    $("#complaint-form").on("submit", function (e) {
      e.preventDefault();
      const formData = $(this).serialize();

      $.ajax({
        url: wvr_frontend_vars.ajax_url,
        type: "POST",
        data: {
          action: "submit_complaint",
          data: formData,
        },
        success: function (response) {
          if (response.success) {
            alert("Your complaint has been submitted successfully.");
            $("#complaint-form")[0].reset(); // Clear the form
          } else {
            alert("Error: " + response.data);
          }
        },
      });
    });
  }

  if (document.getElementById("payments-container")) {
    // View Details Popup
    $(".btn-view-details").on("click", function () {
      const paymentId = $(this).data("payment-id");
      // console.log(`Payment ID = ${paymentId}`);

      $.ajax({
        url: wvr_frontend_vars.ajax_url,
        type: "POST",
        data: {
          action: "get_payment_details_for_frontend",
          payment_id: paymentId,
        },
        success: function (response) {
          // console.log(response);
          if (response.success) {
            $("#payment-details-content").html(response.data);
            $("#payment-details-popup").fadeIn();
          } else {
            alert("Error: " + response.data.message);
          }
        },
      });
    });

    // Close Popup
    $("#close-popup-btn").on("click", function () {
      $("#payment-details-popup").fadeOut();
    });

    // Make Payment (Stripe)
    $("#make-payment-btn").on("click", function () {
      // Initialize Stripe
      const stripe = Stripe(stripePublishableKey);
      const elements = stripe.elements();
      const cardElement = elements.create("card");
      cardElement.mount("#stripe-card-element");

      // Show Stripe Payment Form
      $("#stripe-payment-form").show();

      // Handle Form Submission
      $("#stripe-form").on("submit", function (e) {
        e.preventDefault();
        stripe
          .createPaymentMethod({
            type: "card",
            card: cardElement,
          })
          .then(function (result) {
            if (result.error) {
              alert(result.error.message);
            } else {
              // Send payment method ID to server
              $.ajax({
                url: wvr_frontend_vars.ajax_url,
                type: "POST",
                data: {
                  action: "process_stripe_payment",
                  payment_method_id: result.paymentMethod.id,
                  payment_id: paymentId,
                },
                success: function (response) {
                  if (response.success) {
                    alert("Payment successful!");
                    location.reload();
                  } else {
                    alert("Error: " + response.data);
                  }
                },
              });
            }
          });
      });
    });

    // Download Invoice
    $("#download-invoice-btn").on("click", function () {
      const paymentId = $(".btn-view-details").data("payment-id");
      window.location.href =
        `${wvr_frontend_vars.ajax_url}?action=download_invoice&payment_id=` +
        paymentId;
    });
  }

  if (document.getElementById("booking-container")) {
    // View Details Popup
    $(".btn-view-details").on("click", function () {
      const bookingId = $(this).data("booking-id");

      $.ajax({
        url: wvr_frontend_vars.ajax_url,
        type: "POST",
        data: {
          action: "get_booking_details_for_frontend",
          booking_id: bookingId,
        },
        success: function (response) {
          if (response.success) {
            $("#booking-details-content").html(response.data);
            $("#booking-details-popup").fadeIn();
          } else {
            alert("Error: " + response.data);
          }
        },
      });
    });

    // Close Popup
    $("#close-popup-btn").on("click", function () {
      $("#booking-details-popup").fadeOut();
    });
  }

  if (document.getElementById("single-vehicle")) {
    function setGalleryWidth() {
      // Get the .image-part element
      const imagePart = document.querySelector(".image-part");

      // Get the .gallery-images element
      const galleryImages = document.querySelector(".gallery-images");

      if (imagePart && galleryImages) {
        // Calculate the width of .image-part
        const imagePartWidth = imagePart.offsetWidth;

        // Set the width of .gallery-images to match .image-part
        galleryImages.style.width = `${imagePartWidth}px`;
      }
    }

    // Function to update the height of .tab-contents
    function updateTabHeight() {
      const $activeContent = $(".tab-content.active");
      const newHeight = $activeContent.outerHeight(); // Get the height of the active content
      $(".tab-contents").css("height", newHeight); // Set the height of .tab-contents
    }

    // Handle tab button clicks
    $(".tab-button").on("click", function () {
      // Remove active class from all buttons
      $(".tab-button").removeClass("active");

      // Add active class to the clicked button
      $(this).addClass("active");

      // Get the target tab content
      const tabId = $(this).data("tab");
      const $targetContent = $(`#${tabId}`);

      // Fade out the currently active content
      $(".tab-content.active").css({
        opacity: 0,
        visibility: "hidden",
      });

      // After the fade-out transition, switch the active class
      setTimeout(() => {
        $(".tab-content").removeClass("active");
        $targetContent.addClass("active").css({
          opacity: 1,
          visibility: "visible",
        });

        // Update the height of .tab-contents
        updateTabHeight();
      }, 300); // Match the duration of the CSS transition (0.3s)
    });

    // Initialize the height on page load
    updateTabHeight();

    // Update the height on window resize
    $(window).on("resize", updateTabHeight);

    // Handle click on gallery images
    $(".gallery-img").on("click", function () {
      const newSrc = $(this).attr("src"); // Get the src of the clicked image

      // Fade out the main image
      $(".main-img").css("opacity", 0);

      // After 0.1s, update the src and fade it back in
      setTimeout(() => {
        $(".main-img").attr("src", newSrc).css("opacity", 1);
      }, 100); // 0.1 seconds delay
    });

    // Check user logged in
    // Show error notification if no package is selected
    $("#book-now").on("click", function (e) {
      e.preventDefault();
      if (!$('input[name="package"]:checked').length) {
        $("#error-notification").fadeIn().delay(3000).fadeOut();
        return;
      }

      // Check if user is logged in
      $.ajax({
        url: wvr_frontend_vars.ajax_url,
        type: "POST",
        data: {
          action: "check_user_logged_in",
        },
        success: function (response) {
          if (response.logged_in) {
            // User is logged in, show payment popup
            create_payment_session();
          } else {
            // User is not logged in, show login form
            $("#login-form").show();
            $("#error-notification")
              .text("You must be logged in")
              .fadeIn()
              .delay(3000)
              .fadeOut();
            return;
          }
        },
      });
    });

    // Handle login form submission
    $("#login-form-inner").on("submit", function (e) {
      e.preventDefault();
      const email = $('input[name="email"]').val();
      const password = $('input[name="password"]').val();
      const lg_btn = $("#quick-login");

      $.ajax({
        url: wvr_frontend_vars.ajax_url,
        type: "POST",
        data: {
          action: "user_login",
          user_email: email,
          password: password,
        },
        beforeSend: function () {
          lg_btn
            .prop("disabled", true)
            .text("Processing...")
            .css("background-color", "#242424");
        },
        success: function (response) {
          if (response.success) {
            lg_btn
              .prop("disabled", true)
              .text("Reloading....")
              .css("background-color", "#242424");

            $("#error-notification")
              .text("Login Successful...")
              .css("background", "#28a745")
              .fadeIn()
              .delay(3000)
              .fadeOut();

            location.reload(); // Reload the page after successful login
          } else {
            lg_btn
              .prop("disabled", false)
              .text("Login")
              .css("background-color", "#6772e5");
            alert("Login failed. Please check your credentials.");
          }
        },
      });
    });

    function create_payment_session() {
      // Find the selected radio button
      const selectedRadio = $('input[name="package"]:checked');

      // Get the text of the associated .radio-label span
      const packageText = selectedRadio
        .closest("label")
        .find(".radio-label")
        .text();

      const btn = $("#book-now");

      const selectedPackage = $('input[name="package"]:checked')
        .closest("label")
        .find(".radio-label strong")
        .text();
      // console.log(selectedPackage); // Debug: Check the extracted value
      // console.log(packageText.trim());

      // Extract the amount from the selected package
      const amount = parseFloat(selectedPackage.replace("$", "")) * 100; // Convert to cents
      // console.log(amount); // Debug: Check the extracted amount

      if (isNaN(amount)) {
        alert("Invalid package selection. Please try again.");
        return;
      }

      $.ajax({
        url: wvr_frontend_vars.ajax_url,
        type: "POST",
        data: {
          action: "create_checkout_session",
          amount: amount,
          currency: "usd",
          vehicle_id: wvr_frontend_vars.current_post_id,
          package: selectedPackage,
          packageText: packageText.trim(),
        },
        beforeSend: function () {
          btn
            .prop("disabled", true)
            .text("Processing...")
            .css("background-color", "#242424");
        },
        success: function (response) {
          // console.log(response);
          btn.prop("disabled", true).text("Redirecting...");
          window.location.href = response.data.url; // Redirect to Stripe Checkout
        },
        error: function (error) {
          alert("Error: " + error.responseJSON.data);
        },
      });
    }
  }
});
