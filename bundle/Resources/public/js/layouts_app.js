function callback() {
  const contentBrowser = document.getElementsByClassName(
    'js-content-browser'
  )[0];
  if (contentBrowser !== undefined && contentBrowser.dataset.itemType === 'remote_media_image') {
    const cbChildren = Array.from(
      contentBrowser.getElementsByTagName('ul')
    );
    const pagerIndex = cbChildren
      .map(e => e.attributes['data-cy'].nodeValue)
      .indexOf('pagination');
    if (pagerIndex > -1) {
      const pagerItems = cbChildren[pagerIndex].children;
      for (let i = pagerItems.length - 2; i > 0; i--) {
        pagerItems[i].remove();
      }
    }
  }
}

const observer = new MutationObserver(callback);

const targetNode = document.body;

observer.observe(targetNode, { childList: true, subtree: true });
