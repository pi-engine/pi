(function () {
    var a = {
            exec:function(editor) {
              var b= $(editor.document.$).find("body");
               if(b.find(".pagebreak").length){
                  editor.insertHtml("<p class='pagebreak page-title'></p><p></p>");
               }else{
                  b.prepend("<p class='pagebreak page-title'><br/></p><p></p>");
                  editor.insertHtml("<p class='pagebreak page-title'></p><p></p>");
              }
               
            }
        },
        b = "articlepagebreak";
    CKEDITOR.plugins.add(b, {
        init:function (editor) {
            editor.addCommand(b, a);
            editor.ui.addButton("articlepagebreak", {
                label: "articlepagebreak",
                icon: this.path + 'assets/articlepagebreak.gif',
                command: b
            });
        }
    });
})();