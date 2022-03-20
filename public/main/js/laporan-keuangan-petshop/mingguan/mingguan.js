$(document).ready(function () {
  let optCabang = "";
  let paramUrlSetup = {
    orderby: "",
    column: "",
    date_from: "",
    date_to: "",
    branchId: "",
  };

  if (role.toLowerCase() != "admin") {
    window.location.href = $(".baseUrl").val() + `/unauthorized`;
  } else {
    loadCabang();
    $("#filterCabang").select2({ placeholder: "Cabang", allowClear: true });

    loadLaporanKeuanganMingguan();

    const openDatePicker = window.innerWidth < 768 ? "left" : "right";

    $("#datepicker").daterangepicker({
      autoUpdateInput: false,
      opens: openDatePicker,
      applyClass: "btn-info",
      // showDropdowns: true,
      dateLimit: { days: 7 },
      drops: "auto",
      locale: { format: "YYYY-MM-DD", cancelLabel: "Clear" },
    });
  }

  $("#filterCabang").on("select2:select", function () {
    onFilterCabang($(this).val());
  });
  $("#filterCabang").on("select2:unselect", function () {
    onFilterCabang($(this).val());
  });

  $('input[id="datepicker"]').on(
    "apply.daterangepicker",
    function (ev, picker) {
      const getStartDate = picker.startDate.format("YYYY-MM-DD");
      const getEndDate = picker.endDate.format("YYYY-MM-DD");
      $(this).val(getStartDate + " - " + getEndDate);

      paramUrlSetup.date_from = getStartDate;
      paramUrlSetup.date_to = getEndDate;
      loadLaporanKeuanganMingguan();
    }
  );

  $('input[id="datepicker"]').on(
    "cancel.daterangepicker",
    function (ev, picker) {
      $(this).val("");
      paramUrlSetup.date_from = "";
      paramUrlSetup.date_to = "";
      loadLaporanKeuanganMingguan();
    }
  );

  $(".btn-download-laporan").click(function () {
    $.ajax({
      url: $(".baseUrl").val() + "/api/laporan-keuangan-mingguan/generate",
      headers: { Authorization: `Bearer ${token}` },
      type: "GET",
      data: {
        orderby: paramUrlSetup.orderby,
        column: paramUrlSetup.column,
        date_from: paramUrlSetup.date_from,
        date_to: paramUrlSetup.date_to,
        branch_id: paramUrlSetup.branchId,
      },
      xhrFields: { responseType: "blob" },
      beforeSend: function () {
        $("#loading-screen").show();
      },
      success: function (data, status, xhr) {
        let disposition = xhr.getResponseHeader("content-disposition");
        let matches = /"([^"]*)"/.exec(disposition);
        let filename = matches != null && matches[1] ? matches[1] : "file.xlsx";
        let blob = new Blob([data], {
          type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        });
        let downloadUrl = URL.createObjectURL(blob);
        let a = document.createElement("a");

        a.href = downloadUrl;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
      },
      complete: function () {
        $("#loading-screen").hide();
      },
      error: function (err) {
        if (err.status == 401) {
          localStorage.removeItem("vet-shop");
          location.href = $(".baseUrl").val() + "/masuk";
        }
      },
    });
  });

  $(".onOrdering").click(function () {
    const column = $(this).attr("data");
    const orderBy = $(this).attr("orderby");
    $('.onOrdering[data="' + column + '"]')
      .children()
      .remove();

    if (orderBy == "none" || orderBy == "asc") {
      $(this).attr("orderby", "desc");
      $(this).append('<span class="fa fa-sort-desc"></span>');
    } else if (orderBy == "desc") {
      $(this).attr("orderby", "asc");
      $(this).append('<span class="fa fa-sort-asc"></span>');
    }

    paramUrlSetup.orderby = $(this).attr("orderby");
    paramUrlSetup.column = column;

    loadLaporanKeuanganMingguan();
  });

  function onFilterCabang(value) {
    paramUrlSetup.branchId = value;
    loadLaporanKeuanganMingguan();
  }

  function loadLaporanKeuanganMingguan() {
    $.ajax({
      url: $(".baseUrl").val() + "/api/laporan-keuangan-mingguan",
      headers: { Authorization: `Bearer ${token}` },
      type: "GET",
      data: {
        orderby: paramUrlSetup.orderby,
        column: paramUrlSetup.column,
        date_from: paramUrlSetup.date_from,
        date_to: paramUrlSetup.date_to,
        branch_id: paramUrlSetup.branchId,
      },
      beforeSend: function () {
        $("#loading-screen").show();
      },
      success: function (resp) {
        const getData = resp.data;
        let listLaporanKeuanganMingguan = "";

        $("#list-laporan-keuangan-mingguan tr").remove();

        if (getData.length) {
          $.each(getData, function (idx, v) {
            listLaporanKeuanganMingguan +=
              `<tr>` +
              `<td>${++idx}</td>` +
              `<td>${v.created_at}</td>` +
              `<td>${v.branch_name}</td>` +
              `<td>${v.payment_number}</td>` +
              `<td>${v.item_name}</td>` +
              `<td>${v.category}</td>` +
              `<td>${v.total_item}</td>` +
              `<td>Rp ${
                typeof v.capital_price == "string"
                  ? v.capital_price
                      .toString()
                      .replace(/\B(?=(\d{3})+(?!\d))/g, ".")
                  : ""
              }</td>` +
              `<td>Rp ${
                typeof v.selling_price == "string"
                  ? v.selling_price
                      .toString()
                      .replace(/\B(?=(\d{3})+(?!\d))/g, ".")
                  : ""
              }</td>` +
              `<td>Rp ${
                typeof v.profit == "string"
                  ? v.profit.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
                  : ""
              }</td>` +
              `<td>Rp ${
                typeof v.overall_price == "string"
                  ? v.overall_price
                      .toString()
                      .replace(/\B(?=(\d{3})+(?!\d))/g, ".")
                  : ""
              }</td>` +
              `<td>${v.created_by}</td>` +
              `</tr>`;
          });
        } else {
          listLaporanKeuanganMingguan += `<tr class="text-center"><td colspan="14">Tidak ada data.</td></tr>`;
        }
        $("#list-laporan-keuangan-mingguan").append(
          listLaporanKeuanganMingguan
        );

        const capitalPrice = resp.capital_price
          ? resp.capital_price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
          : "-";
        const sellingPrice = resp.selling_price
          ? resp.selling_price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
          : "-";
        const profit = resp.profit
          ? resp.profit.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
          : "";

        $("#harga-modal-txt").text(`Rp. ${capitalPrice}`);
        $("#harga-jual-txt").text(`Rp. ${sellingPrice}`);
        $("#keuntungan-txt").text(`Rp. ${profit}`);
      },
      complete: function () {
        $("#loading-screen").hide();
      },
      error: function (err) {
        if (err.status == 401) {
          localStorage.removeItem("vet-shop");
          location.href = $(".baseUrl").val() + "/masuk";
        }
      },
    });
  }

  function loadCabang() {
    $.ajax({
      url: $(".baseUrl").val() + "/api/cabang",
      headers: { Authorization: `Bearer ${token}` },
      type: "GET",
      beforeSend: function () {
        $("#loading-screen").show();
      },
      success: function (data) {
        optCabang += `<option value=''>Cabang</option>`;

        if (data.length) {
          for (let i = 0; i < data.length; i++) {
            optCabang += `<option value=${data[i].id}>${data[i].branch_name}</option>`;
          }
        }
        $("#filterCabang").append(optCabang);
      },
      complete: function () {
        $("#loading-screen").hide();
      },
      error: function (err) {
        if (err.status == 401) {
          localStorage.removeItem("vet-shop");
          location.href = $(".baseUrl").val() + "/masuk";
        }
      },
    });
  }
});
