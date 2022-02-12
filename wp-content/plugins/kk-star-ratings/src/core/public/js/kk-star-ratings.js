/**
 * kk Star Ratings
 * @see https://github.com/kamalkhan/kk-star-ratings
 */

"use strict";

(function (fn) {
  if (document.readyState != "loading") {
    return fn();
  }

  document.addEventListener("DOMContentLoaded", fn);
})(function kkStarRatings() {
  var isBusy = false;

  function post(data, successCallback, errorCallback) {
    if (isBusy) {
      return;
    }

    isBusy = true;

    data = Object.assign(
      {
        nonce: kk_star_ratings.nonce,
        action: kk_star_ratings.action,
      },
      data
    );

    var query = [];
    for (var key in data) {
      query.push(encodeURIComponent(key) + "=" + encodeURIComponent(data[key]));
    }

    var request = new XMLHttpRequest();

    request.open("POST", kk_star_ratings.endpoint, true);

    request.onload = function () {
      if (request.status >= 200 && request.status < 400) {
        successCallback(request.responseText, request);
      } else {
        errorCallback(request.responseText, request);
      }
    };

    request.onloadend = function () {
      isBusy = false;
    };

    request.setRequestHeader(
      "Content-type",
      "application/x-www-form-urlencoded; charset=UTF-8"
    );

    request.send(query.join("&"));
  }

  function htmlToEl(html) {
    var div = document.createElement("div");
    div.innerHTML = html.trim();

    return div.firstChild;
  }

  function apply($el) {
    function vote($star) {
      var data = {
        rating: $star.getAttribute("data-star"),
      };

      var payload = JSON.parse($el.getAttribute("data-payload"));

      for (var key in payload) {
        data["payload[" + key + "]"] = payload[key];
      }

      post(
        data,
        function (html) {
          var $newEl = htmlToEl(html);
          $el.parentNode.replaceChild($newEl, $el);
          unmount();
          $el = null;
          apply($newEl);
        },
        console.error
      );
    }

    function onClick(e) {
      e.preventDefault();
      vote(e.currentTarget);
    }

    var $stars = $el.querySelectorAll("[data-star]");

    function unmount() {
      Array.prototype.forEach.call($stars, function ($star) {
        $star.removeEventListener("click", onClick);
      });
    }

    function mount() {
      Array.prototype.forEach.call($stars, function ($star) {
        $star.addEventListener("click", onClick);
      });
    }

    mount();
  }

  Array.prototype.forEach.call(
    document.querySelectorAll(".kk-star-ratings"),
    apply
  );
});
