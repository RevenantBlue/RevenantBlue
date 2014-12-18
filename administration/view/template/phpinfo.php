<html>
	<body>
		<div id="left">
		<div class="links-left">
		<center>
		{block:ifCustomLinkOneTitle}
		<a href="{text:Custom Link One}">{text:Custom Link One Title}</a><p> {/block:ifCustomLinkOneTitle}
		{block:ifCustomLinkTwoTitle}
		<a href="{text:Custom Link Two}">{text:Custom Link Two Title}</a><p> {/block:ifCustomLinkTwoTitle}
		{block:ifCustomLinkThreeTitle}
		<a href="{text:Custom Link Three}">{text:Custom Link Three Title}</a><p> {/block:ifCustomLinkThreeTitle}
		{block:ifCustomLinkFourTitle}
		<a href="{text:Custom Link Four}">{text:Custom Link Four Title}</a> <p> {/block:ifCustomLinkFourTitle}
		{block:ifShowAsk}<a href="/ask">Ask Questions</a><p>{/block:ifShowAsk}
		<a href="/submit">Submit Anything</a><p>
		{block:ifShowArchive}<a href="/archive">Archive</a><p>{/block:ifShowArchive}
		<a href="/">Home</a> <p>
		<p>
		</center>
		</div></div>
	</body>
</html>
