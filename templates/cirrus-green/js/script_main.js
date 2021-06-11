jQuery(document).ready(function ($) {
  /*MENU RESPONSIVO*/
  $(".menuresp li.parent > a, .menuresp li.parent > span").append(
    ' <button type="button"><i class="fas fa-chevron-down"></i></button>'
  );

  $(".menuresp li.parent > span").click(function () {
    $(this).siblings("ul").slideToggle();
    $(this)
      .children("button")
      .find("i")
      .toggleClass("fa-chevron-up", "fa-chevron-down");
  });

  $(window)
    .on("resize", function () {
      $(".menuresp").css(
        "max-height",
        $(window).height() - $("#header").height()
      );

      var menuresp = $(".gotomenu");
      menuresp.each(function () {
        if (!$(this).is(":visible")) {
          $(".menuresp").hide();
          $("#nav-icon").removeClass("open");
        }
      });
    })
    .trigger("resize");

  $(".menuresp").hide();

  $("#gotomenu").click(function () {
    $(".menuresp").slideToggle();
  });

  if (!home) {
    $("#header_wrap").css("background-color", "#FFF");
    if ($("#logo").hasClass("white")) {
      $("#logo").removeClass("white");
    }
    $(
      "#topmenu a, #topmenu li.active a, #topmenu li.active a:hover, #topmenu span, #topmenu li.active span, #topmenu li.active span:hover"
    ).css("color", "#504f51");
    $("#topmenu li ul").css("background-color", "#FFF");
    if (!$("#topmenu a").hasClass("border-grey")) {
      $("#topmenu a").addClass("border-grey");
    }
    if (!$("ul.contact-social li a").hasClass("color-blue")) {
      $("ul.contact-social li a").addClass("color-blue");
    }
    if (!$(".btn.btn-cotacao").hasClass("border-blue")) {
      $(".btn.btn-cotacao").addClass("border-blue");
      $(".btn.btn-cotacao").addClass("color-blue");
    }
    $("#header_wrap").next().css("margin-top", $("#header").height());

    //Menu responsivo
    $(".menuresp ul").css("background-color", "transparent");
    if (!$("#gotomenu i").hasClass("color-blue")) {
      $("#gotomenu i").addClass("color-blue");
    }
    if (!$("#gotomenu").hasClass("border-blue")) {
      $("#gotomenu").addClass("border-blue");
    }
    $(".menuresp a, .menuresp span, .menuresp ul li button").css(
      "color",
      "#504f51"
    );
  }

  $(window)
    .on("resize", function () {
      $(window).scroll(function () {
        var scrollTop = $(window).scrollTop();
        if (home) {
          if (scrollTop >= 90) {
            $("#header_wrap").css("background-color", "#FFF");
            if ($("#logo").hasClass("white")) {
              $("#logo").removeClass("white");
            }
            $(
              "#topmenu a, #topmenu li.active a, #topmenu li.active a:hover, #topmenu span, #topmenu li.active span, #topmenu li.active span:hover"
            ).css("color", "#504f51");
            $("#topmenu li ul").css("background-color", "#FFF");
            if (!$("#topmenu a").hasClass("border-grey")) {
              $("#topmenu a").addClass("border-grey");
            }
            if (!$("ul.contact-social li a").hasClass("color-blue")) {
              $("ul.contact-social li a").addClass("color-blue");
            }

            if (!$(".btn.btn-cotacao").hasClass("border-blue")) {
              $(".btn.btn-cotacao").addClass("border-blue");
              $(".btn.btn-cotacao").addClass("color-blue");
            }

            //Menu responsivo
            $(".menuresp ul").css("background-color", "transparent");
            if (!$("#gotomenu i").hasClass("color-blue")) {
              $("#gotomenu i").addClass("color-blue");
            }
            if (!$("#gotomenu").hasClass("border-blue")) {
              $("#gotomenu").addClass("border-blue");
            }
            $(".menuresp a, .menuresp span, .menuresp ul li button").css(
              "color",
              "#504f51"
            );
          } else {
            $("#header_wrap").css("background-color", "transparent");
            if (!$("#logo").hasClass("white")) {
              $("#logo").addClass("white");
            }
            $(
              "#topmenu a, #topmenu li.active a, #topmenu li.active a:hover, #topmenu span, #topmenu li.active span, #topmenu li.active span:hover"
            ).css("color", "#FFF");
            $("#topmenu li ul").css("background-color", "rgba(0,0,0,0.5)");
            if ($("#topmenu a").hasClass("border-grey")) {
              $("#topmenu a").removeClass("border-grey");
            }
            if ($("ul.contact-social li a").hasClass("color-blue")) {
              $("ul.contact-social li a").removeClass("color-blue");
            }

            if ($(".btn.btn-cotacao").hasClass("border-blue")) {
              $(".btn.btn-cotacao").removeClass("border-blue");
              $(".btn.btn-cotacao").removeClass("color-blue");
            }

            //Menu responsivo
            $(".menuresp ul").css("background-color", "rgba(0,0,0,0.5)");
            if ($("#gotomenu i").hasClass("color-blue")) {
              $("#gotomenu i").removeClass("color-blue");
            }
            if ($("#gotomenu").hasClass("border-blue")) {
              $("#gotomenu").removeClass("border-blue");
            }
            $(".menuresp a, .menuresp span, .menuresp ul li button").css(
              "color",
              "#FFF"
            );
          }
        } else {
          $("#header_wrap").next().css("margin-top", $("#header").height());
        }
      });
    })
    .trigger("resize");
});
