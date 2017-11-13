global _ft_memset

section .text

_ft_memset:
	push rdi
	cmp rdi, 0
	je return
	mov rax, rsi
	mov rcx, rdx
	cld
	rep stosb

return:
	pop rax
	ret