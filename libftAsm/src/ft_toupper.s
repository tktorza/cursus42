section .text
global _ft_toupper
_ft_toupper:
	mov rax, rdi
	cmp rdi, 96
	jg toup
	ret

toup:
	cmp rdi, 123
	jl ok
	ret
ok:
	sub rdi, 32
	mov rax, rdi
	ret