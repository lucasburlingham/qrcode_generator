---
description: This applies to all HTML files to preserve syntax and style
# applyTo: 'This applies to all HTML files to preserve syntax and style' # when provided, instructions will automatically be added to the request context when the pattern matches an attached file
---

When editing or creating an html file, make sure you're using valid HTML 5 format. Start with the header, like this:

```html
<!doctype html>
<html lang="en-US">
  <head>
    <meta charset="UTF-8" />
    <meta
  name="viewport"
  content="width=device-width, initial-scale=1.0, viewport-fit=cover interactive-widget=resizes-content" />
    <title>Document title</title>
    <meta name="cache-buster" content="<!-- epoch time -->" />
  </head>
  <body>
    <!-- Main Body content goes here -->
  </body>
</html>
```

Within the body tags, separate sections of the page with section tags, to include header main and footer when appropriate. Use div tags to separate content within the sections, and use semantic tags like article, aside, nav, and header when appropriate. Always close your tags, and use indentation to make the structure of the document clear.

When specified to keep code clean and simple, use <style> and <script> tags. Avoid using external CSS and JavaScript files unless necessary for the functionality of the page. When using inline scripts, ensure they are well-organized and do not clutter the HTML structure. Do not use inline styles or scripts.

The ui should be rendered with HTML as much as possible to reduce computational load on the client. Avoid using complex JavaScript frameworks or libraries for simple UI elements, and instead rely on HTML and CSS to create the desired look and feel. When using JavaScript, keep it simple and efficient, and avoid unnecessary complexity that can slow down the page. When using CSS, keep it simple and efficient as well. Avoid using complex selectors or properties that can slow down the rendering of the page. Use classes and IDs to target specific elements, and avoid using inline styles unless necessary for the functionality of the page. When using CSS, also consider the accessibility of the page, and ensure that it is usable for all users, including those with disabilities. Use semantic HTML tags to improve the accessibility of the page, and ensure that the page is navigable with a keyboard and screen reader. When using JavaScript, also consider the accessibility of the page,and ensure that it is usable for all users, including those with disabilities. Use ARIA attributes to improve the accessibility of the page, and ensure that the page is navigable with a keyboard and screen reader. When using JavaScript, also consider the performance of the page, and ensure that it loads quickly and efficiently. 

I'd prefer you to keep all html files under 10KB if possible.

Mobile clients should be considered when designing the UI, and the page should be responsive to different screen sizes, but you should avoid using media queries.

Use #ids when a element is truely unique on the page. Use .classes when you want to reuse styles across multiple elements. Avoid using inline styles unless necessary for the functionality of the page, and instead use classes and IDs to target specific elements. When using classes and IDs, keep them concise and descriptive to improve readability and maintainability of the code.

If you have questions, ASK. Do not lie.