window.addEventListener("load", function () {
  var settings = document.querySelector(".github-sync-settings");
  var formGroups = document.querySelectorAll(".form-group");
  var submit = document.querySelector(".submit");
  var instructions = document.querySelector(".instructions");

  setTimeout(function () {
    settings.classList.add("fadeIn");
  }, 100);

  formGroups.forEach(function (formGroup, index) {
    setTimeout(function () {
      formGroup.classList.add("slideIn");
    }, 200 * (index + 1));
  });

  setTimeout(function () {
    submit.classList.add("slideIn");
  }, 200 * (formGroups.length + 1));

  setTimeout(function () {
    instructions.classList.add("fadeIn");
  }, 200 * (formGroups.length + 2));
});

function github_sync_clear_logs(plugin_dir_path) {
  var logContainer = document.querySelector(".log-container textarea");
  logContainer.value = "";

  // Send AJAX request to clear the log file
  var xhr = new XMLHttpRequest();
  xhr.open("POST", ajaxurl, true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      console.log("Logs cleared successfully");
    }
  };
  xhr.send(
    "action=github_sync_clear_logs&plugin_dir_path=" +
      encodeURIComponent(plugin_dir_path)
  );
}
