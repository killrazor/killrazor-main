filetype off
filetype plugin indent off
set runtimepath+=$GOROOT/misc/vim
filetype plugin on
syntax on

set autoindent
set smartindent
set pastetoggle=<F2>
filetype plugin indent on

colorscheme elflord
autocmd FileType * setlocal formatoptions-=c formatoptions-=r formatoptions-=o
" <F7>
au FileType java            inoremap <F7> System.err.println( "" + );<Esc>5hi
au FileType javascript,html inoremap <F7> console.log( '', );<Esc>4hi
" <F9>
au FileType java,javascript,cs,php noremap <F9> :'a,.s/^/\/\//g<CR>:nohl<CR>
au FileType tcl noremap <F9> :'a,.s/^/#/g<CR>:nohl<CR>
" <F10>
au FileType * noremap <F10> :'a,.s/^/\t/g<CR>:nohl<CR>

function FormatText()
        " add a space after '(' if there is any character other than ')'
        %s/\((\)\([^\ |)]\)/\1 \2/ge
        " add a space before ')' if there is any character other than '('
        %s/\([^\ |(]\)\()\)/\1 \2/ge
        " add a space after comma
        %s/\(,\)\([^\ ]\)/\1 \2/ge
        " 'e' added to the end of 'g' to supress warnings.
        " to expand upon this, I would like to know when I was inside quotes and not do it there. I also want to fix nesting, tabs/space at the end of the line, and maybe spaces that should be tabs.
endfunction
" <F11>
noremap <F11> :call FormatText()<CR>:nohl<CR>
