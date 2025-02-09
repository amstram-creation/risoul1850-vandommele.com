'use strict';

export default class Activities {
  constructor(selector) {
    document.querySelector(selector).addEventListener('click', async (e) => {
      e.preventDefault();
      let activities = await this.fetch();
      this.render(activities);
    });
  }

  render(activities) {
    const card = document.querySelector('.activities-cards .activity-card');
    const fragment = document.createDocumentFragment();
    let clone;
    activities.forEach((activity) => {
      clone = card.cloneNode(true);
      clone.setAttribute('href', activity.href);
      clone.querySelector('p').remove();

      clone.querySelector('img').setAttribute('src', activity.src);
      clone.querySelector('h3').textContent = activity.title;
      fragment.appendChild(clone);
    });
    let container = document.querySelector('.activities-cards');
    container.innerHTML = '';
    container.appendChild(fragment);
  }

  async fetch() {
    let title, href, img;
    const skipURL = [
      'https://www.risoul.com/billetterie.html',
      'https://www.risoul.com/risoul-1850.html',
      'https://www.risoul.com/activites-hiver.html?theme=HIVER',
      'https://www.risoul.com/activites-ete.html?theme=ETE',
    ];
    let sources = [
      {
        url: 'https://en.risoul.com/winter.html',
        selector: '.sommaire_cadre',
        title: 'h2',
        href: 'a',
        img: 'img',
        category: 'winter',
      },
      {
        url: 'https://www.risoul.com/activites-ete.html',
        selector: '.sommaire_cadre',
        title: 'h2',
        href: 'a',
        img: 'img',
        category: 'summer',
      },
    ];

    let activities = []

    let _;
    let parser = new DOMParser();
    let doc;
    for (const season of sources) {
      _ = await fetch(season.url);
      _ = await _.text();

      doc = parser.parseFromString(_, 'text/html');

      Array.from(doc.querySelectorAll('.sommaire_cadre')).forEach(
        (element, index) => {
          href = element.querySelector(season.href).getAttribute('href');
          if (!skipURL.includes(href)) {
            img = element.querySelector(season.img).getAttribute('src');
            title = element.querySelector(season.title).textContent;
            activities.push({
              title: title,
              href: href,
              src: img,
              category: season.category,
            });
          }
        }
      );
    }
    return activities;
  }
}
