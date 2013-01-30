function togglePannel( chevron)
    {
        var content = chevron.parentNode.nextSibling.nextSibling;
        var expand = (content.style.display=="none") | (content.style.display=="");
        content.style.display = (expand ? "block" : "none");
        chevron.src = chevron.src
            .split(expand ? "expand.gif" : "collapse.gif")
            .join(expand ? "collapse.gif" : "expand.gif");
    }