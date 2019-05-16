function deletePubMysql(queryStr,filePath){
  jQuery.ajax({
    type: "POST",
    url: 'functions.php',
    data: {functionname: 'deletePubMysql', arguments: [queryStr,filePath]}
  });
}

