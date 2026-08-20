Jekyll::Hooks.register :pages, :post_render do |page|
  next unless page.output.include?("[kbtoc]")

  headings = page.output.scan(/<h2 id="([^"]+)">(.*?)<\/h2>/)
  toc = +"<nav class=\"kbtoc\"><strong>On this page</strong><ul>"
  headings.each do |id, text|
    toc << "<li><a href=\"##{id}\">#{text}</a></li>"
  end
  toc << "</ul></nav>"

  page.output.sub!("<p>[kbtoc]</p>", toc)
end
