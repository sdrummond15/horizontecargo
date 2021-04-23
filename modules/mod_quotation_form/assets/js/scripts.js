jQuery(document).ready(function ($) {
  var url = window.location.pathname;
  var pathname = window.location.pathname.indexOf("index.php");
  var path = "";
  if (pathname >= 0) {
    path = url.substr("0", pathname);
  } else {
    path = "";
  }

  $("#msg-box .fechar").on("click", function () {
    $("#msg-box").slideUp();
  });

  $("#telefone").mask("(00) 0000-00000");

  $("#form-quotation").on("submit", function (event) {
    event.preventDefault();

    var formData = new FormData($(this)[0]);

    $("#msg-box").slideUp();
    $("#msg").html("");
    var msg = [];
    var focus = "";
    if (
      isEmail(formData.get("email")) == false ||
      formData.get("email").length == 0
    ) {
      msg.push("Informe um e-mail válido!");
      focus = "email";
    }
    if (formData.get("telefone").length < 14) {
      msg.push("Informe um telefone válido!");
      focus = "telefone";
    }
    if (formData.get("reponsavel").length < 3) {
      msg.push("Informe um reponsável válido!");
      focus = "reponsavel";
    }
    if (formData.get("empresa").length < 2) {
      msg.push("Informe uma empresa válida!");
      focus = "empresa";
    }
    if (
      formData.get("anexo").name &&
      formData.get("anexo").size &&
      formData.get("anexo").type != "text/csv" &&
      formData.get("anexo").type != "application/msword" &&
      formData.get("anexo").type != "image/gif" &&
      formData.get("anexo").type != "image/x-icon" &&
      formData.get("anexo").type != "image/jpeg" &&
      formData.get("anexo").type != "image/png" &&
      formData.get("anexo").type != "application/pdf" &&
      formData.get("anexo").type != "application/vnd.ms-powerpoint" &&
      formData.get("anexo").type != "image/svg+xml" &&
      formData.get("anexo").type != "image/webp" &&
      formData.get("anexo").type != "application/vnd.ms-excel"
    ) {
      msg.push("Tipo de arquivo inválido!");
      focus = "anexo";
    }
    if (formData.get("descricao").length == 0) {
      msg.push("Informe a descrição da carga!");
      focus = "descricao";
    }
    if (formData.get("altura").length == 0) {
      msg.push("Informe uma altura válida!");
      focus = "altura";
    }
    if (formData.get("largura").length == 0) {
      msg.push("Informe uma largura válida!");
      focus = "largura";
    }
    if (formData.get("comprimento").length == 0) {
      msg.push("Informe um comprimento válido!");
      focus = "comprimento";
    }
    if (formData.get("quantidade").length == 0) {
      msg.push("Informe uma quantidade válida!");
      focus = "quantidade";
    }
    if (formData.get("peso_bruto").length == 0) {
      msg.push("Informe um peso bruto válido!");
      focus = "peso_bruto";
    }
    if (formData.get("cuidado").length == 0) {
      msg.push("Informe um cuidado válido!");
      focus = "cuidado";
    }
    if (formData.get("destino").length < 3) {
      msg.push("Informe o destino!");
      focus = "destino";
    }
    if (formData.get("origem").length < 3) {
      msg.push("Informe a origem !");
      focus = "origem";
    }
    if (
      isValidDate(formData.get("de")) &&
      formData.get("de").length != 0
    ) {
      msg.push("Informe uma data válida!");
      focus = "de";
    }

    if (msg.length) {
      msg.reverse();
      msg.forEach(mensagens);
      function mensagens(item, index) {
        $("#msg").append("<p>" + item + "</p>");
      }
      $("html, body").animate(
        {
          scrollTop: $("#quotation").offset().top - $("#header").height() - 10,
        },
        300,
        function () {
          $("#msg-box").slideDown("slow");
        }
      );
      $("#msg-box").removeClass("alert-success").addClass("alert-danger");
      $("#" + focus).focus();

      return false;
    }

    $.ajax({
      type: "POST",
      url: path + "modules/mod_quotation_form/tmpl/sendmail.php",
      async: true,
      data: formData,
      processData: false,
      contentType: false,
      success: function (data) {
        $("#msg-box").removeClass("alert-danger").addClass("alert-" + data.class);
        $("#msg").append(data);
      },
      beforeSend: function () {
        $(".loading").fadeIn("fast");
      },
      complete: function () {
        // $("#form-quotation")[0].reset();
      },
    });
  });

  function isValidDate(txtDate) {
    var currVal = txtDate;
    if (currVal == "") {
      return false;
    }

    //Declare Regex
    var rxDatePattern = "/^(d{1,2})(/|-)(d{1,2})(/|-)(d{4})$/";
    var dtArray = currVal.match(rxDatePattern); // is format OK?

    if (dtArray == null) {
      return false;
    }

    //Checks for dd/mm/yyyy format.
    dtDay = dtArray[1];
    dtMonth = dtArray[3];
    dtYear = dtArray[5];

    if (dtMonth < 1 || dtMonth > 12) {
      return false;
    } else if (dtDay < 1 || dtDay > 31) {
      return false;
    } else if (
      (dtMonth == 4 || dtMonth == 6 || dtMonth == 9 || dtMonth == 11) &&
      dtDay == 31
    ) {
      return false;
    } else if (dtMonth == 2) {
      var isleap = dtYear % 4 == 0 && (dtYear % 100 != 0 || dtYear % 400 == 0);
      if (dtDay > 29 || (dtDay == 29 && !isleap)) {
        return false;
      }
    }
    return true;
  }

  function isEmail(email) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
  }
});
