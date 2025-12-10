document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');

    function applyTheme(theme){
        if(theme === "dark"){
            body.classList.add("dark-mode");
            localStorage.setItem("theme","dark");
            if(themeIcon) themeIcon.setAttribute("name","sunny-outline");
        } else {
            body.classList.remove("dark-mode");
            localStorage.setItem("theme","light");
            if(themeIcon) themeIcon.setAttribute("name","contrast-outline");
        }
    }

    const saved = localStorage.getItem("theme");
    applyTheme(saved ?? (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark":"light"));

    if(themeToggle){
        themeToggle.addEventListener("click",()=>{
            const isDark = body.classList.contains("dark-mode");
            applyTheme(isDark ? "light" : "dark");
        });
    }
});
