import { PortfPubPage } from './app.po';

describe('portf-pub App', () => {
  let page: PortfPubPage;

  beforeEach(() => {
    page = new PortfPubPage();
  });

  it('should display message saying app works', () => {
    page.navigateTo();
    expect(page.getParagraphText()).toEqual('app works!');
  });
});
