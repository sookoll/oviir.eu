#!/usr/bin/env python
# -*- coding: utf-8 -*-

import urllib.request, json
import os

script_dir = os.path.dirname(__file__)

template = {
    'Title': '',
    'Description': 'Oviir.eu suguvõsa veebileht',
    'Date': '2000-07-01',
    'Image': '',
    'Thumbnail': '',
    'Template': 'album',
    'Category': 'pildialbum',
    'MiuView': {
        'url': 'http://oviir.eu/miuview-api',
        'album': ''
    }
}

with urllib.request.urlopen("http://oviir.eu/miuview-api/?request=getalbum&album=*&size=1200&thsize=360&key=") as url:
    data = json.loads(url.read().decode())
    for album in data['albums']:
        filename = album['id'] + '.md'
        file_path = os.path.join(script_dir, '..', 'src', 'content', 'pildid', filename)
        f = open(file_path, 'w+')
        template['Title'] = album['title']
        template['Image'] = album['thumb_url'].replace("size=360", "size=1200").replace("mode=square", "mode=longest")
        template['Thumbnail'] = album['thumb_url']
        template['MiuView']['album'] = album['id']
        f.write("---\r\n")
        for key, value in template.items():
            if isinstance(value, dict):
                f.write(key + ":\r\n")
                for key2, value2 in value.items():
                    f.write("  " + key2 + ": " + value2 + "\r\n")
            else:
                f.write(key + ": " + value + "\r\n")
        f.write("---\r\n")
        f.close()
        print('File {} done'.format(filename))
    print('done')
        