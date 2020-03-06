
export const config = {
    toolbarGroups: [
        { name: 'document', groups: ['mode', 'document', 'doctools'] },
        {
            name: 'editing',
            groups: ['find', 'selection', 'spellchecker', 'editing']
        },
        { name: 'forms', groups: ['forms'] },
        { name: 'basicstyles', groups: ['basicstyles', 'cleanup'] },
        {
            name: 'paragraph',
            groups: ['list', 'indent', 'blocks', 'align', 'bidi', 'paragraph']
        },
        '/',
        { name: 'links', groups: ['links'] },
        { name: 'insert', groups: ['insert'] },
        { name: 'styles', groups: ['styles'] },
        { name: 'colors', groups: ['colors'] },
        { name: 'tools', groups: ['tools'] },
        '/',
        { name: 'clipboard', groups: ['clipboard', 'undo'] },
        { name: 'others', groups: ['others'] },
        { name: 'about', groups: ['about'] }
    ],
    removeButtons:
        'Save,NewPage,Preview,Print,Templates,Cut,Copy,Paste,PasteText,PasteFromWord,Find,SelectAll,Scayt,Replace,Form,Checkbox,Textarea,Select,Button,ImageButton,HiddenField,CreateDiv,BidiLtr,BidiRtl,Language,Flash,Smiley,SpecialChar,PageBreak,Iframe,Anchor,ShowBlocks,About,CopyFormatting,Undo,Redo',
    fontSize_sizes: '16/16px;24/24px;48/48px;',
    font_names:
    'Arial/Arial, Helvetica, sans-serif;' +
    'Times New Roman/Times New Roman, Times, serif;' +
    'Verdana',
    allowedContent: true
    // disableNativeSpellChecker: false
    // skin: "moono",
    // plugins:
    //   "dialogui,dialog,about,a11yhelp,dialogadvtab,basicstyles,bidi,blockquote,notification,button,toolbar,clipboard,panelbutton,panel,floatpanel,colorbutton,colordialog,templates,menu,contextmenu,copyformatting,div,resize,elementspath,enterkey,entities,popup,filetools,filebrowser,find,fakeobjects,flash,floatingspace,listblock,richcombo,font,forms,format,horizontalrule,htmlwriter,iframe,wysiwygarea,image,indent,indentblock,indentlist,smiley,justify,menubutton,language,link,list,liststyle,magicline,maximize,newpage,pagebreak,pastetext,pastefromword,preview,print,removeformat,save,selectall,showblocks,showborders,sourcearea,specialchar,scayt,stylescombo,tab,table,tabletools,tableselection,undo,lineutils,widgetselection,widget,notificationaggregator,uploadwidget,uploadimage,wsc"
}

  onChange(evt) {
    // console.log("onChange fired with event info: ", evt);
    var html = evt.editor.getData();
    console.log("this", this);
    this.setState({ html });
  }

  onBlur(evt) {
    console.log("onBlur event called with event info: ", evt);
  }

  afterPaste(evt) {
    console.log("afterPaste event called with event info: ", evt);
  }

  render() {
    return (
      <CKEditor
        activeClass="p10"
        config={config}
        content={this.state.html}
        // onChange={this.onChange}
        events={{
          blur: this.onBlur,
          afterPaste: this.afterPaste,
          change: this.onChange
        }}
      />
    );
  }
}
