const HEADER = [['Tanggal', 'Nomor Surat', 'Kepada', 'Perihal', 'Nama Pengaju', 'Konseptor', 'Arsip', 'TTD']];

function doPost(e) {
  var data = e.parameter;
  var kategori = data.kategori;
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName(kategori);
  if (!sheet) {
    sheet = ss.insertSheet(kategori);
    sheet.appendRow(HEADER[0]);
  }
  var newRow = [
    data.tanggal,
    data.nomor_surat,
    data.kepada,
    data.perihal,
    data.nama_pengaju,
    data.konseptor,
    data.arsip,
    ""
  ];
  sheet.appendRow(newRow);
  return ContentService.createTextOutput(JSON.stringify({ "status": "success" }));
}

function doGet(e) {
  try {
    var params = e.parameter;
    var action = params.action;
    if (action === 'getAllData') {
      return getAllData();
    }

    var nomorSuratToUpdate = params.nomorSurat;
    if (!nomorSuratToUpdate) {}

    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var allSheets = ss.getSheets();
    var columnIndex, newValue;

    if (action === 'updateStatus') {
      columnIndex = 7;
      newValue = params.status;
    } else if (action === 'updateTTD') {
      columnIndex = 8;
      newValue = params.ttd;
    } else {}

    for (var s = 0; s < allSheets.length; s++) {
      var sheet = allSheets[s];
      var data = sheet.getDataRange().getValues();
      for (var i = 1; i < data.length; i++) {
        if (data[i][1] && data[i][1].toString().trim().toLowerCase() == nomorSuratToUpdate.trim().toLowerCase()) { 
          sheet.getRange(i + 1, columnIndex).setValue(newValue);
          return ContentService.createTextOutput(JSON.stringify({ "status": "success" }));
        }
      }
    }
  } catch (error) {}
}

function getAllData() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var allSheets = ss.getSheets();
  var dataBySheet = {};
  for (var s = 0; s < allSheets.length; s++) {
    var sheet = allSheets[s];
    var sheetName = sheet.getName();
    if (sheet.getName().toLowerCase() === 'sheet1' || sheet.getLastRow() < 2) continue;

    var range = sheet.getRange(2, 1, sheet.getLastRow() - 1, sheet.getLastColumn());
    var values = range.getValues();
    var formattedData = [];
    for (var i = 0; i < values.length; i++) {
      var row = values[i];
      var dateCell = row[0];
      if (dateCell instanceof Date) {
        row[0] = Utilities.formatDate(dateCell, "GMT+7", "yyyy-MM-dd");
        formattedData.push(row);
      }
    }
    if (formattedData.length > 0) {
      dataBySheet[sheetName] = formattedData;
    }
  }
  return ContentService.createTextOutput(JSON.stringify(dataBySheet)).setMimeType(ContentService.MimeType.JSON);
}