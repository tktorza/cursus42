section .text
global _ft_isascii
_ft_isascii:
	cmp rdi, 0
	jl no
	cmp rdi, 127
	jg no

yes:
	mov rax, 1
	ret

no:
	mov rax, 0
	ret