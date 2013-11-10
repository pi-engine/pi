(function() {
  var eefocus = this.eefocus || {};
  var EEFOCUS_DATA = {
    'country': {
      "\u4e2d\u56fd": {
        "\u5317\u4eac": ["\u4e1c\u57ce", "\u897f\u57ce", "\u5d07\u6587", "\u5ba3\u6b66", "\u671d\u9633", "\u4e30\u53f0", "\u77f3\u666f\u5c71", "\u6d77\u6dc0", "\u95e8\u5934\u6c9f", "\u623f\u5c71", "\u901a\u5dde", "\u987a\u4e49", "\u660c\u5e73", "\u5927\u5174", "\u5e73\u8c37", "\u6000\u67d4", "\u5bc6\u4e91", "\u5ef6\u5e86"],
        "\u4e0a\u6d77": ["\u9ec4\u6d66", "\u5362\u6e7e", "\u5f90\u6c47", "\u957f\u5b81", "\u9759\u5b89", "\u666e\u9640", "\u95f8\u5317", "\u8679\u53e3", "\u6768\u6d66", "\u95f5\u884c", "\u5b9d\u5c71", "\u5609\u5b9a", "\u6d66\u4e1c", "\u91d1\u5c71", "\u677e\u6c5f", "\u9752\u6d66", "\u5357\u6c47", "\u5949\u8d24", "\u5d07\u660e"],
        "\u5929\u6d25": ["\u548c\u5e73", "\u4e1c\u4e3d", "\u6cb3\u4e1c", "\u897f\u9752", "\u6cb3\u897f", "\u6d25\u5357", "\u5357\u5f00", "\u5317\u8fb0", "\u6cb3\u5317", "\u6b66\u6e05", "\u7ea2\u6322", "\u5858\u6cbd", "\u6c49\u6cbd", "\u5927\u6e2f", "\u5b81\u6cb3", "\u9759\u6d77", "\u5b9d\u577b", "\u84df\u53bf"],
        "\u91cd\u5e86": ["\u4e07\u5dde", "\u6daa\u9675", "\u6e1d\u4e2d", "\u5927\u6e21\u53e3", "\u6c5f\u5317", "\u6c99\u576a\u575d", "\u4e5d\u9f99\u5761", "\u5357\u5cb8", "\u5317\u789a", "\u4e07\u76db", "\u53cc\u6322", "\u6e1d\u5317", "\u5df4\u5357", "\u9ed4\u6c5f", "\u957f\u5bff", "\u7da6\u6c5f", "\u6f7c\u5357", "\u94dc\u6881", "\u5927\u8db3", "\u8363\u660c", "\u58c1\u5c71", "\u6881\u5e73", "\u57ce\u53e3", "\u4e30\u90fd", "\u57ab\u6c5f", "\u6b66\u9686", "\u5fe0\u53bf", "\u5f00\u53bf", "\u4e91\u9633", "\u5949\u8282", "\u5deb\u5c71", "\u5deb\u6eaa", "\u77f3\u67f1", "\u79c0\u5c71", "\u9149\u9633", "\u5f6d\u6c34", "\u6c5f\u6d25", "\u5408\u5ddd", "\u6c38\u5ddd", "\u5357\u5ddd"],
        "\u6cb3\u5317": ["\u77f3\u5bb6\u5e84", "\u90af\u90f8", "\u90a2\u53f0", "\u4fdd\u5b9a", "\u5f20\u5bb6\u53e3", "\u627f\u5fb7", "\u5eca\u574a", "\u5510\u5c71", "\u79e6\u7687\u5c9b", "\u6ca7\u5dde", "\u8861\u6c34"],
        "\u5c71\u897f": ["\u592a\u539f", "\u5927\u540c", "\u9633\u6cc9", "\u957f\u6cbb", "\u664b\u57ce", "\u6714\u5dde", "\u5415\u6881", "\u5ffb\u5dde", "\u664b\u4e2d", "\u4e34\u6c7e", "\u8fd0\u57ce"],
        "\u5185\u8499\u53e4": ["\u547c\u548c\u6d69\u7279", "\u5305\u5934", "\u4e4c\u6d77", "\u8d64\u5cf0", "\u547c\u4f26\u8d1d\u5c14\u76df", "\u963f\u62c9\u5584\u76df", "\u54f2\u91cc\u6728\u76df", "\u5174\u5b89\u76df", "\u4e4c\u5170\u5bdf\u5e03\u76df", "\u9521\u6797\u90ed\u52d2\u76df", "\u5df4\u5f66\u6dd6\u5c14\u76df", "\u4f0a\u514b\u662d\u76df"],
        "\u8fbd\u5b81": ["\u6c88\u9633", "\u5927\u8fde", "\u978d\u5c71", "\u629a\u987a", "\u672c\u6eaa", "\u4e39\u4e1c", "\u9526\u5dde", "\u8425\u53e3", "\u961c\u65b0", "\u8fbd\u9633", "\u76d8\u9526", "\u94c1\u5cad", "\u671d\u9633", "\u846b\u82a6\u5c9b"],
        "\u5409\u6797": ["\u957f\u6625", "\u5409\u6797", "\u56db\u5e73", "\u8fbd\u6e90", "\u901a\u5316", "\u767d\u5c71", "\u677e\u539f", "\u767d\u57ce", "\u5ef6\u8fb9"],
        "\u9ed1\u9f99\u6c5f": ["\u54c8\u5c14\u6ee8", "\u9f50\u9f50\u54c8\u5c14", "\u7261\u4e39\u6c5f", "\u4f73\u6728\u65af", "\u5927\u5e86", "\u7ee5\u5316", "\u9e64\u5c97", "\u9e21\u897f", "\u9ed1\u6cb3", "\u53cc\u9e2d\u5c71", "\u4f0a\u6625", "\u4e03\u53f0\u6cb3", "\u5927\u5174\u5b89\u5cad"],
        "\u6c5f\u82cf": ["\u5357\u4eac", "\u9547\u6c5f", "\u82cf\u5dde", "\u5357\u901a", "\u626c\u5dde", "\u76d0\u57ce", "\u5f90\u5dde", "\u8fde\u4e91\u6e2f", "\u5e38\u5dde", "\u65e0\u9521", "\u5bbf\u8fc1", "\u6cf0\u5dde", "\u6dee\u5b89"],
        "\u6d59\u6c5f": ["\u676d\u5dde", "\u5b81\u6ce2", "\u6e29\u5dde", "\u5609\u5174", "\u6e56\u5dde", "\u7ecd\u5174", "\u91d1\u534e", "\u8862\u5dde", "\u821f\u5c71", "\u53f0\u5dde", "\u4e3d\u6c34"],
        "\u5b89\u5fbd": ["\u5408\u80a5", "\u829c\u6e56", "\u868c\u57e0", "\u9a6c\u978d\u5c71", "\u6dee\u5317", "\u94dc\u9675", "\u5b89\u5e86", "\u9ec4\u5c71", "\u6ec1\u5dde", "\u5bbf\u5dde", "\u6c60\u5dde", "\u6dee\u5357", "\u5de2\u6e56", "\u961c\u9633", "\u516d\u5b89", "\u5ba3\u57ce", "\u4eb3\u5dde"],
        "\u798f\u5efa": ["\u798f\u5dde", "\u53a6\u95e8", "\u8386\u7530", "\u4e09\u660e", "\u6cc9\u5dde", "\u6f33\u5dde", "\u5357\u5e73", "\u9f99\u5ca9", "\u5b81\u5fb7"],
        "\u6c5f\u897f": ["\u5357\u660c\u5e02", "\u666f\u5fb7\u9547", "\u4e5d\u6c5f", "\u9e70\u6f6d", "\u840d\u4e61", "\u65b0\u9980", "\u8d63\u5dde", "\u5409\u5b89", "\u5b9c\u6625", "\u629a\u5dde", "\u4e0a\u9976"],
        "\u5c71\u4e1c": ["\u6d4e\u5357", "\u9752\u5c9b", "\u6dc4\u535a", "\u67a3\u5e84", "\u4e1c\u8425", "\u70df\u53f0", "\u6f4d\u574a", "\u6d4e\u5b81", "\u6cf0\u5b89", "\u5a01\u6d77", "\u65e5\u7167", "\u83b1\u829c", "\u4e34\u6c82", "\u5fb7\u5dde", "\u804a\u57ce", "\u6ee8\u5dde", "\u83cf\u6cfd"],
        "\u6cb3\u5357": ["\u90d1\u5dde", "\u5f00\u5c01", "\u6d1b\u9633", "\u5e73\u9876\u5c71", "\u5b89\u9633", "\u9e64\u58c1", "\u65b0\u4e61", "\u7126\u4f5c", "\u6fee\u9633", "\u8bb8\u660c", "\u6f2f\u6cb3", "\u4e09\u95e8\u5ce1", "\u5357\u9633", "\u5546\u4e18", "\u4fe1\u9633", "\u5468\u53e3", "\u9a7b\u9a6c\u5e97", "\u6d4e\u6e90"],
        "\u6e56\u5317": ["\u6b66\u6c49", "\u5b9c\u660c", "\u8346\u5dde", "\u8944\u6a0a", "\u9ec4\u77f3", "\u8346\u95e8", "\u9ec4\u5188", "\u5341\u5830", "\u6069\u65bd", "\u6f5c\u6c5f", "\u5929\u95e8", "\u4ed9\u6843", "\u968f\u5dde", "\u54b8\u5b81", "\u5b5d\u611f", "\u9102\u5dde"],
        "\u6e56\u5357": ["\u957f\u6c99", "\u5e38\u5fb7", "\u682a\u6d32", "\u6e58\u6f6d", "\u8861\u9633", "\u5cb3\u9633", "\u90b5\u9633", "\u76ca\u9633", "\u5a04\u5e95", "\u6000\u5316", "\u90f4\u5dde", "\u6c38\u5dde", "\u6e58\u897f", "\u5f20\u5bb6\u754c"],
        "\u5e7f\u4e1c": ["\u5e7f\u5dde", "\u6df1\u5733", "\u73e0\u6d77", "\u6c55\u5934", "\u4e1c\u839e", "\u4e2d\u5c71", "\u4f5b\u5c71", "\u97f6\u5173", "\u6c5f\u95e8", "\u6e5b\u6c5f", "\u8302\u540d", "\u8087\u5e86", "\u60e0\u5dde", "\u6885\u5dde", "\u6c55\u5c3e", "\u6cb3\u6e90", "\u9633\u6c5f", "\u6e05\u8fdc", "\u6f6e\u5dde", "\u63ed\u9633", "\u4e91\u6d6e"],
        "\u5e7f\u897f": ["\u5357\u5b81", "\u67f3\u5dde", "\u6842\u6797", "\u68a7\u5dde", "\u5317\u6d77", "\u9632\u57ce\u6e2f", "\u94a6\u5dde", "\u8d35\u6e2f", "\u7389\u6797", "\u5357\u5b81\u5730\u533a", "\u67f3\u5dde\u5730\u533a", "\u8d3a\u5dde", "\u767e\u8272", "\u6cb3\u6c60"],
        "\u6d77\u5357": ["\u6d77\u53e3", "\u4e09\u4e9a"],
        "\u56db\u5ddd": ["\u6210\u90fd", "\u7ef5\u9633", "\u5fb7\u9633", "\u81ea\u8d21", "\u6500\u679d\u82b1", "\u5e7f\u5143", "\u5185\u6c5f", "\u4e50\u5c71", "\u5357\u5145", "\u5b9c\u5bbe", "\u5e7f\u5b89", "\u8fbe\u5ddd", "\u96c5\u5b89", "\u7709\u5c71", "\u7518\u5b5c", "\u51c9\u5c71", "\u6cf8\u5dde"],
        "\u8d35\u5dde": ["\u8d35\u9633", "\u516d\u76d8\u6c34", "\u9075\u4e49", "\u5b89\u987a", "\u94dc\u4ec1", "\u9ed4\u897f\u5357", "\u6bd5\u8282", "\u9ed4\u4e1c\u5357", "\u9ed4\u5357"],
        "\u4e91\u5357": ["\u6606\u660e", "\u5927\u7406", "\u66f2\u9756", "\u7389\u6eaa", "\u662d\u901a", "\u695a\u96c4", "\u7ea2\u6cb3", "\u6587\u5c71", "\u601d\u8305", "\u897f\u53cc\u7248\u7eb3", "\u4fdd\u5c71", "\u5fb7\u5b8f", "\u4e3d\u6c5f", "\u6012\u6c5f", "\u8fea\u5e86", "\u4e34\u6ca7"],
        "\u897f\u85cf": ["\u62c9\u8428", "\u65e5\u5580\u5219", "\u5c71\u5357", "\u6797\u829d", "\u660c\u90fd", "\u963f\u91cc", "\u90a3\u66f2"],
        "\u9655\u897f": ["\u897f\u5b89", "\u5b9d\u9e21", "\u54b8\u9633", "\u94dc\u5ddd", "\u6e2d\u5357", "\u5ef6\u5b89", "\u6986\u6797", "\u6c49\u4e2d", "\u5b89\u5eb7", "\u5546\u6d1b"],
        "\u7518\u8083": ["\u5170\u5dde", "\u5609\u5cea\u5173", "\u91d1\u660c", "\u767d\u94f6", "\u5929\u6c34", "\u9152\u6cc9", "\u5f20\u6396", "\u6b66\u5a01", "\u5b9a\u897f", "\u9647\u5357", "\u5e73\u51c9", "\u5e86\u9633", "\u4e34\u590f", "\u7518\u5357"],
        "\u5b81\u590f": ["\u94f6\u5ddd", "\u77f3\u5634\u5c71", "\u5434\u5fe0", "\u56fa\u539f"],
        "\u9752\u6d77": ["\u897f\u5b81", "\u6d77\u4e1c", "\u6d77\u5357", "\u6d77\u5317", "\u9ec4\u5357", "\u7389\u6811", "\u679c\u6d1b", "\u6d77\u897f"],
        "\u65b0\u7586": ["\u4e4c\u9c81\u6728\u9f50", "\u77f3\u6cb3\u5b50", "\u514b\u62c9\u739b\u4f9d", "\u4f0a\u7281", "\u5df4\u97f3\u90ed\u52d2", "\u660c\u5409", "\u514b\u5b5c\u52d2\u82cf\u67ef\u5c14\u514b\u5b5c", "\u535a \u5c14\u5854\u62c9", "\u5410\u9c81\u756a", "\u54c8\u5bc6", "\u5580\u4ec0", "\u548c\u7530", "\u963f\u514b\u82cf"],
        "\u9999\u6e2f": ["\u9999\u6e2f\u7279\u522b\u884c\u653f\u533a"],
        "\u6fb3\u95e8": ["\u6fb3\u95e8\u7279\u522b\u884c\u653f\u533a"],
        "\u53f0\u6e7e": ["\u53f0\u5317", "\u9ad8\u96c4", "\u53f0\u4e2d", "\u53f0\u5357", "\u5c4f\u4e1c", "\u5357\u6295", "\u4e91\u6797", "\u65b0\u7af9", "\u5f70\u5316", "\u82d7\u6817", "\u5609\u4e49", "\u82b1\u83b2", "\u6843\u56ed", "\u5b9c\u5170", "\u57fa\u9686", "\u53f0\u4e1c", "\u91d1\u95e8", "\u9a6c\u7956", "\u6f8e\u6e56"]
      },
      "\u7f8e\u56fd": {
        "Alaska": [],
        "Alabama": [],
        "West Virginia": [],
        "Wyoming": [],
        "Arizona": [],
        "Arkansas": [],
        "California": [],
        "Colorado": [],
        "Florida": [],
        "Georgia": [],
        "Hawaii": [],
        "Idaho": [],
        "Illinois": [],
        "Iowa": [],
        "Kansas": [],
        "Lousiana": [],
        "Maine": [],
        "Michigan": [],
        "Maryland": [],
        "Minnesota": [],
        "Mississippi": [],
        "Missouri": [],
        "Montana": [],
        "Nevada": [],
        "New Hampshire": [],
        "New  Jeresy": [],
        "New York": [],
        "North Carolina": [],
        "North Dakota": [],
        "Ohio": [],
        "Oregon": [],
        "Pennsylvania": [],
        "Rhode Island": [],
        "South Carolina": [],
        "Texas": [],
        "Utah": [],
        "Vermont": [],
        "Washington": [],
        "Massachusetts": [],
        "Connecticut": [],
        "South Dakota ": [],
        "Nebraska": [],
        "Kentucky": [],
        "Virginia": [],
        "Indiana": [],
        "Delawar": [],
        "New Mexico": [],
        "Oklahoma": [],
        "Tennessee": [],
        "Wisconsin": []
      },
      "\u65e5\u672c": [],
      "\u97e9\u56fd": [],
      "\u53f0\u6e7e": [],
      "\u9999\u6e2f": [],
      "\u82f1\u56fd": [],
      "\u6cd5\u56fd": [],
      "\u5fb7\u56fd": [],
      "\u6fb3\u5927\u5229\u4e9a": [],
      "\u52a0\u62ff\u5927": [],
      "\u745e\u58eb": [],
      "\u8377\u5170": [],
      "\u4ee5\u8272\u5217": [],
      "\u65b0\u52a0\u5761": [],
      "\u5370\u5ea6": [],
      "\u5965\u5730\u5229": [],
      "\u4fc4\u7f57\u65af": [],
      "\u82ac\u5170": [],
      "\u7231\u5c14\u5170": [],
      "\u632a\u5a01": [],
      "\u610f\u5927\u5229": [],
      "\u897f\u73ed\u7259": [],
      "\u745e\u5178": [],
      "\u65b0\u897f\u5170": [],
      "\u4e39\u9ea6": [],
      "\u8461\u8404\u7259": [],
      "\u5357\u975e": []
    },
    'industry': {
      "\u901a\u4fe1\/\u5e7f\u64ad": ["\u6709\u7ebf\u7f51\u7edc\u7cfb\u7edf", "\u65e0\u7ebf\u7f51\u7edc\u7cfb\u7edf", "\u5e7f\u64ad\u7535\u89c6\u8bbe\u5907"],
      "\u5de5\u4e1a\u7535\u5b50": ["\u673a\u5668\u4eba", "\u6570\u636e\u91c7\u96c6", "\u5de5\u4e1a\u63a7\u5236", "\u5b89\u9632\/\u76d1\u63a7"],
      "\u6d88\u8d39\u7535\u5b50": ["\u767d\u7535\/\u5c0f\u5bb6\u7535", "\u5bb6\u5ead\u5f71\u9662 ", "\u65e0\u7ebf\u624b\u6301\u8bbe\u5907", "\u6570\u5b57\u6210\u50cf\u8bbe\u5907"],
      "\u533b\u7597\u7535\u5b50": ["\u533b\u7597\u6210\u50cf", "\u76d1\u62a4\u4e0e\u8bca\u65ad\u8bbe\u5907", "\u4fbf\u643a\u53ca\u5bb6\u5ead\u62a4\u7406"],
      "\u6c7d\u8f66\u7535\u5b50": ["\u8f66\u8eab\u63a7\u5236", "\u5bfc\u822a\/\u4fe1\u606f\/\u5a31\u4e50", "\u6c7d\u8f66\u5b89\u5168", "\u52a8\u529b\u4e0e\u7167\u660e"],
      "\u80fd\u6e90\/\u65b0\u80fd\u6e90": ["\u667a\u80fd\u7535\u7f51", "\u80fd\u91cf\u8ba1\u91cf", "\u5149\u4f0f\/\u53ef\u518d\u751f\u80fd\u6e90", "\u8282\u80fd\/\u7167\u660e"],
      "\u822a\u7a7a\/\u822a\u6d77\/\u519b\u5de5\u7535\u5b50": [],
      "\u7269\u8054\u7f51": ["RFID\u6807\u7b7e\u53ca\u8bfb\u5199\u8bbe\u5907", "\u4f20\u611f\u7f51\u7edc\/\u4fe1\u606f\u91c7\u96c6\u7cfb\u7edf", "\u4fe1\u606f\u6570\u636e\u5904\u7406\u7cfb\u7edf", "\u79fb\u52a8\u652f\u4ed8"],
      "\u8ba1\u7b97\u673a\u53ca\u5468\u8fb9\u7cfb\u7edf": ["PC\/\u670d\u52a1\u5668", "PC\u5916\u8bbe\/\u529e\u516c\u8bbe\u5907"],
      "\u5de5\u5177\u5382\u5546": ["\u8f6f\u4ef6", "\u6d4b\u8bd5\/\u6d4b\u91cf\u4eea\u5668", "EDA\u5de5\u5177"],
      "\u884c\u4e1a\u670d\u52a1": ["\u4ee3\u7406\/\u5206\u9500", "PCB\u670d\u52a1", "\u534a\u5bfc\u4f53\u5236\u9020", "\u9ad8\u6821\u9662\u6240\/\u653f\u5e9c"]
    },
    'position': ["\u7814\u53d1","\u7814\u53d1\u7ba1\u7406\/\u9879\u76ee\u7ba1\u7406","\u6d4b\u8bd5\u6d4b\u91cf","\u751f\u4ea7\u7ba1\u7406\/\u8d28\u91cf\u63a7\u5236","\u91c7\u8d2d\/\u5e02\u573a\/\u670d\u52a1","\u5b66\u751f"],
    "interest": [
      "\u4f20\u611f\/MEMS", 
      "\u653e\u5927\u548c\u7ebf\u6027\u4ea7\u54c1",
      "\u6570\u636e\u8f6c\u6362\u5668",
      "\u7535\u6e90\/\u7535\u6c60\u7ba1\u7406",
      "\u6570\u5b57\/\u53ef\u7f16\u7a0b\u903b\u8f91",
      "\u65f6\u949f\/\u5b9a\u65f6",
      "\u63a7\u5236\u5668\/\u5904\u7406\u5668\/DSP",
      "\u5b58\u50a8",
      "\u63a5\u53e3",
      "\u97f3\/\u89c6\u9891",
      "\u5206\u7acb\/\u65e0\u6e90\u5668\u4ef6",
      "\u4fdd\u62a4\/\u9694\u79bb",
      "\u8fde\u63a5\u5668",
      "\u5149\u7535\/\u663e\u793a",
      "RF\/\u5fae\u6ce2",
      "\u901a\u4fe1\/\u7f51\u7edcIC",
      "\u5f00\u5173\/\u591a\u8def\u590d\u7528\u5668",
      "\u6d4b\u8bd5\u6d4b\u91cf",
      "\u5d4c\u5165\u5f0f\u5f00\u53d1\u5de5\u5177",
      "EDA\/IP\/IC\/PCB\u8bbe\u8ba1",
      "\u5236\u9020\/\u5c01\u88c5"
    ],
    "subscription": [
      "\u5d4c\u5165\u5f0f\u7cfb\u7edf\u8bbe\u8ba1",
      "\u6d4b\u8bd5\u6d4b\u91cf",
      "\u6a21\u62df\u0026\u7535\u6e90",
      "\u6d88\u8d39\u7535\u5b50",
      "\u5de5\u4e1a\u7535\u5b50",
      "\u7eff\u8272\u8bbe\u8ba1",
      "\u7535\u5b50\u8d44\u8baf\u96c6\u9526",
      "\u65e0\u7ebf\u0026\u5c04\u9891"
    ]
  }
  /*
    <div id="js-country-element" data-value="上海">
    </div>
    <script>
      new eefocus.Linkage("js-country-element", ["country", "province", "city"]);
    </script>
    <div id="js-industry-element" data-value="医疗电子">
    </div>
    <script>
      new eefocus.Linkage("js-industry-element", ["industry", "sphere"]);
    </script>
   */
  eefocus.Linkage = function(root, names) {
    this.el = $('#' + root);
    this.data = EEFOCUS_DATA[names[0]];
    this.names = names;
    var self = this;
    $(function() {
      self.init();
    });
  }

  eefocus.Linkage.prototype = {
    init: function() {
      var html = '';
      var length = this.names.length;
      var form = this.el.parents('form');
      var self = this
      $.each(this.names, function(index, item) {
          html += '<select name=' + item + ' class="input-medium"></select>';
      });
      this.el.html(html);
      this.elements = this.$('select');
      this.events();
      this.render(0).val(this.el.attr('data-value')).trigger('change');
      $.each(this.names, function(index, item) {
        if (index == 0) return;
        var element = self.elements.eq(index);
        element.val(form.find('input[name=' + item + ']').remove().val());
        if (index != length - 1) element.trigger('change');
      });
    },
    $: function(selector) {
      return this.el.find(selector);
    },
    render: function(index) {
      var element = this.elements.eq(index).val('');
      var arr = this.getData(index);
      element.nextAll().val('').hide();
      if (!arr.length) {
        element.hide();
        return;
      }
      var html = '<option value="">请选择</option>';
      $.each(arr, function(index, item) {
          html += "<option value=" + item +">" + item;
      });
      return element.html(html).show();
    },
    getData: function(index) {
      var ret;
      var data = this.data;
      var elements = this.elements;
      if (elements.length == 3) {
        switch (index) {
          case 0:
            ret = this.keys(data);
            break;
          case 1:
            ret = this.keys(data[elements.eq(0).val()]);
            break;
          case 2:
            var firstValue = elements.eq(0).val();
            if (!firstValue) return [];
            ret = data[firstValue][elements.eq(1).val()];
        }
      } else if (elements.length == 2) {
        switch (index) {
          case 0:
            ret = this.keys(data);
            break;
          case 1:
            ret = data[elements.eq(0).val()];
        }
      }
      return ret || [];
    },
    events: function() {
      var length = this.elements.length;
      var self = this;
      this.elements.each(function(index) {
        if (index + 1 == length) return;
        self.elements.eq(index).change(function() {
          self.render(index + 1);
        });
      });
    },
    keys: function(obj) {
      if (!$.isPlainObject(obj)) return [];
      if (Object.keys) {
        return Object.keys(obj);
      } 
      var keys = [];
      for (var i in obj) {
        if (obj.hasOwnProperty(i)) {
          keys.push(i);
        }
      }
      return keys;
    }
  };

  eefocus.Checkbox = function(root, name, values) {
    this.el = $('#' + root);
    this.name = name;
    this.init(values);
  }

  eefocus.Checkbox.prototype = {
    init: function(values) {
      var html = '';
      var name = this.name;
      var data = EEFOCUS_DATA[name];
      $.each(data, function(index ,item) {
        var checked = '';
        if (values && values.indexOf(item) != -1) {
          checked = 'checked="checked"';
        }
        
        html += '<label class="inline checkbox"><input type="checkbox" name="' + name + '[]" value="' + item + '"' + checked + '>' + item + '</label>';
      });
      this.el.html(html);
    }
  }

  this.eefocus = eefocus;
})();
