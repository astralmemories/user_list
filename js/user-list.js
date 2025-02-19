(function ($, Drupal) {
  Drupal.behaviors.user_list = {
    attach: function (context, settings) {
      console.log("✅ User List JavaScript Loaded!");

      $.ajax({
        url: "/user-list/ajax",
        type: "POST",
        data: {},
        success: function (response) {
          console.log("✅ Users fetched:", response);
          
          // Clear the table body before adding new rows
          $("#user-list-table tbody").empty();

          // Check if we have users
          if (response.usuarios && response.usuarios.length > 0) {
            response.usuarios.forEach(function (user) {
              $("#user-list-table tbody").append(`
                <tr>
                  <td>${user.name}</td>
                  <td>${user.surname1}</td>
                  <td>${user.surname2}</td>
                  <td>${user.email}</td>
                </tr>
              `);
            });
          } else {
            $("#user-list-table tbody").append(`
              <tr>
                <td colspan="4">No users found.</td>
              </tr>
            `);
          }
        },
        error: function (error) {
          console.error("❌ Error fetching users:", error);
        }
      });
    }
  };
})(jQuery, Drupal);
