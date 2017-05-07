{extends file="base.tpl"}

{block name="title"} - 404{/block}

{block name="content"}
    <div class="text-center">
        <img src="{$webDir}assets/img/404error.jpg" />
        <h1>Oops, wrong way!</h1>
        <div>
            Looks like the page or file you wanted went missing.<br />
        </div>
    </div>
{/block}