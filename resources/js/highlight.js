/*
 * Syntax highlighting, imported on demand by app.js (a separate chunk, so
 * pages without code never download it). Only the languages used in the
 * documentation are registered.
 */
import hljs from 'highlight.js/lib/core';
import bash from 'highlight.js/lib/languages/bash';
import ini from 'highlight.js/lib/languages/ini';
import javascript from 'highlight.js/lib/languages/javascript';
import json from 'highlight.js/lib/languages/json';
import php from 'highlight.js/lib/languages/php';
import python from 'highlight.js/lib/languages/python';
import http from 'highlight.js/lib/languages/http';
import yaml from 'highlight.js/lib/languages/yaml';

hljs.registerLanguage('bash', bash);
hljs.registerLanguage('ini', ini);
hljs.registerLanguage('javascript', javascript);
hljs.registerLanguage('json', json);
hljs.registerLanguage('php', php);
hljs.registerLanguage('python', python);
hljs.registerLanguage('http', http);
hljs.registerLanguage('yaml', yaml);
hljs.registerAliases(['sh', 'shell', 'console', 'curl'], { languageName: 'bash' });
hljs.registerAliases(['js'], { languageName: 'javascript' });
hljs.registerAliases(['py'], { languageName: 'python' });
hljs.registerAliases(['env', 'dotenv'], { languageName: 'ini' });
hljs.configure({ ignoreUnescapedHTML: true });

export function highlight(code) {
    const language = (code.className.match(/language-([\w-]+)/) || [])[1];

    if (!language || !hljs.getLanguage(language)) {
        return;
    }

    // Highlighting works on the plain text, so nothing from the page is
    // interpreted as HTML.
    const result = hljs.highlight(code.textContent, { language, ignoreIllegals: true });

    code.innerHTML = result.value;
    code.classList.add('hljs');
}
