let theme = localStorage.getItem('blog_theme');

if(!theme)
{
    theme = 'light';
}
else if(theme=='dark')
{
    toggleTheme();
}

function toggleTheme()
{
  if(document.querySelector('body').classList.contains('dark'))
  {
     document.querySelector('body').classList.remove('dark');
     localStorage.setItem('blog_theme','light');
     jQuery(".theme-toggle .target-theme").html("dark");
  }
  else
  {
    document.querySelector('body').classList.add('dark');
    localStorage.setItem('blog_theme','dark');
    jQuery(".theme-toggle .target-theme").html("light");
  }

}