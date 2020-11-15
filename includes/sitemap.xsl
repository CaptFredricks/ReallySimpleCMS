<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" xmlns:html="http://www.w3.org/TR/REC-html40" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>XML Sitemap</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<style>
				html {
					background-color: #2f2f2f;
				}
				
				body {
					color: #fefefe;
					font-family: Helvetica, sans-serif;
					font-size: 100%;
					margin: 0;
				}
				
				h1 {
					font-size: 1.5em;
				}
				
				p {
					font-size: 0.875em;
				}
				
				a {
					color: #b6e0fa;
					text-decoration: none;
				}
				
				a:hover {
					text-decoration: underline;
				}
				
				table {
					background-color: #ededed;
					border-collapse: collapse;
					box-shadow: 0 2px 4px #000;
					color: #101010;
					width: 100%;
				}
				
				table a {
					color: #46708a;
				}
				
				thead tr,
				tbody tr:not(:last-child) {
					border-bottom: 1px solid #cdcdcd;
				}
				
				tbody tr:nth-child(odd) {
					background-color: #ddd;
				}
				
				tbody tr:hover {
					background-color: #fdfdfd;
				}
				
				th,
				td {
					font-size: 0.875em;
					padding: 0.5rem;
				}
				
				th:not(:last-child),
				td:not(:last-child) {
					border-right: 1px solid #cdcdcd;
				}
				
				.wrapper {
					margin: 5em auto;
					max-width: 900px;
					width: 94%;
				}
				</style>
			</head>
			<body>
				<div class="wrapper">
					<h1>XML Sitemap</h1>
					<xsl:if test="count(sitemap:sitemapindex/sitemap:sitemap) &gt; 0">
						<p>This sitemap index contains <xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)" /> URL(s). To learn about what sitemaps do, visit <a href="https://sitemaps.org/" target="_blank" rel="noreferrer noopener">sitemaps.org</a>.</p>
						<table>
							<thead>
								<tr>
									<th>Sitemap</th>
								</tr>
							</thead>
							<tbody>
								<xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
									<tr>
										<td>
											<xsl:variable name="sitemap_url">
												<xsl:value-of select="sitemap:loc" />
											</xsl:variable>
											<a href="{$sitemap_url}">
												<xsl:value-of select="sitemap:loc" />
											</a>
										</td>
									</tr>
								</xsl:for-each>
							</tbody>
						</table>
					</xsl:if>
					<xsl:if test="count(sitemap:sitemapindex/sitemap:sitemap) &lt; 1">
						<p>This sitemap contains <xsl:value-of select="count(sitemap:urlset/sitemap:url)" /> URL(s). To learn about what sitemaps do, visit <a href="https://sitemaps.org/" target="_blank" rel="noreferrer noopener">sitemaps.org</a>.</p>
						<table>
							<thead>
								<tr>
									<th width="80%">URL</th>
									<th width="20%">Last Modified</th>
								</tr>
							</thead>
							<tbody>
								<xsl:for-each select="sitemap:urlset/sitemap:url">
									<tr>
										<td>
											<xsl:variable name="item_url">
												<xsl:value-of select="sitemap:loc" />
											</xsl:variable>
											<a href="{$item_url}">
												<xsl:value-of select="sitemap:loc" />
											</a>
										</td>
										<td>
											<xsl:if test="count(sitemap:lastmod) &gt; 0">
												<xsl:value-of select="concat(substring(sitemap:lastmod,0,11), concat(' @ ', substring(sitemap:lastmod,12,5)))" />
											</xsl:if>
										</td>
									</tr>
								</xsl:for-each>
							</tbody>
						</table>
						<p><a href="/sitemap.xml">Return to the sitemap index</a></p>
					</xsl:if>
				</div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>