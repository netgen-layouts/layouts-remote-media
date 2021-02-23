function callback(records) {
    records.forEach(function (record) {
        var list = record.addedNodes;
        var i = list.length - 1;

        for ( ; i > -1; i-- ) {
            if (typeof list[i].classList != 'undefined' && list[i].classList.contains('js-content-browser')) {
                console.log(list[i]);
                console.log(list[i].getElementsByClassName('Pager_pager__3ZmyX'));
            }
        }
    });
}

var observer = new MutationObserver(callback);

var targetNode = document.body;

observer.observe(targetNode, { childList: true, subtree: true });
