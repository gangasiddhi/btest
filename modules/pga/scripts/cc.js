var ccen = 0;
var cce = new Array ();
cce [0] = "Kredi kartı türü bilinmiyor.";
cce [1] = "Kredi kartı numarası girmediniz. Lütfen tekrar deneyiniz.";
cce [2] = "Kredi kartı numaranızı yanlış formatta girdiniz. Lütfen tekrar deneyiniz.";
cce [3] = "Kredi kartı numaranız geçersiz.";
cce [4] = "Kredi kartı numaranızın gerekli olan 16 haneden daha çok hanesi bulunuyor.";
cce [5] = "CVC numaranızın gerekli olan 3 haneden daha çok hanesi bulunuyor.";
function cccr (ccn, cn) {
 var crd = new Array();
 crd [0] = {nm: "1", pfs: "4", cd: true};
 crd [1] = {nm: "2", pfs: "51,52,53,54,55", cd: true};
 var ctp = -1;
 for (var i=0; i<crd.length; i++) {
  if (cn.toLowerCase() == crd[i].nm.toLowerCase()) {
   ctp = i;
   break;
  }
 }
 if (ctp == -1) {
  ccen = 0;
  return false;
 }
 if (ccn.length == 0) {
  ccen = 1;
  return false;
 }
 var cno = ccn.replace (/\s/g, "");
 var cxp = /^[0-9]{13,19}$/;
 if (!cxp.exec(cno)) {
  ccen = 2;
  return false;
 }
 var lvd = false; var pfv = false; var pf = new Array ();
 pf = crd[ctp].pfs.split(",");
 for (i=0; i<pf.length; i++) {
  var exp = new RegExp ("^" + pf[i]);
  if (exp.test (cno))
   pfv = true;
 }
 if (!pfv) {
  ccen = 3;
  return false;
 }
 if (cno.length == 16)
  lvd = true;
 if (!lvd) {
  ccen = 4;
  return false;
 }
 return true;
}
