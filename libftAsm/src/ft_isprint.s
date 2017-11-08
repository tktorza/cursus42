section .text
global _ft_isprint
_ft_isprint:
	mov rax, 0
	cmp rdi, 31
	jg maybeok
	ret

maybeok:
	cmp rdi, 126
	jl ok
	ret

ok:
	mov rax, 1
	ret